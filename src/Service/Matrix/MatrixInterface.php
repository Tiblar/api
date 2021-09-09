<?php
namespace App\Service\Matrix;

use App\Entity\Application\Application;
use App\Entity\Application\OAuth\Code;
use App\Entity\User\User;
use App\Service\Generator\Snowflake;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class MatrixInterface {

    private EntityManagerInterface $em;
    private string $matrixServer;
    private string $redirectURL;
    private string $environment;
    private FilesystemAdapter $cache;

    /**
     * MatrixAdmin constructor.
     *
     * @param EntityManagerInterface $em
     * @param string $matrixServer
     * @param string $redirectURL
     */
    public function __construct(EntityManagerInterface $em, string $matrixServer, string $redirectURL, string $environment)
    {
        $this->em = $em;
        $this->matrixServer = $matrixServer;
        $this->redirectURL = $redirectURL;
        $this->environment = $environment;
        $this->cache = new FilesystemAdapter();
    }

    public function doesUserExist(string $userId): bool
    {
        $matrixServer = $this->matrixServer;
        $schema = $this->environment === 'dev' ? "http://" : "https://";

        $client = new Client();
        $res = $client->request('GET', $schema . $matrixServer . "/_synapse/admin/v2/users/@tb_" . $userId . ":" . $matrixServer, [
            'http_errors' => false,
            'allow_redirects' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAdminAccessToken(),
            ],
        ]);

        if($res->getStatusCode() >= 200 && $res->getStatusCode() < 204){
            return true;
        }

        return false;
    }

    public function createUser(string $userId): bool
    {
        $matrixServer = $this->matrixServer;
        $schema = $this->environment === 'dev' ? "http://" : "https://";

        $data = $this->getUserTokenAndDevice($userId);

        $client = new Client();
        $res = $client->request('DELETE', $schema . $matrixServer . "/_synapse/admin/v2/users/@tb_" . $userId . ":" . $matrixServer . "/devices/" . $data['device_id'], [
            'http_errors' => false,
            'allow_redirects' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAdminAccessToken(),
            ],
        ]);

        $result = json_decode($res->getBody()->getContents(), true);

        if(json_last_error() === JSON_ERROR_NONE && isset($result['errcode']) && $result['errcode'] === "M_UNKNOWN_TOKEN"){
            $this->refreshAdminAccessToken();
        }

        if($res->getStatusCode() >= 200 && $res->getStatusCode() < 204){
            return true;
        }

        return false;
    }

    /**
     * @param User $user
     * @param array $data
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function updateUser(User $user, array $data): bool
    {
        $matrixServer = $this->matrixServer;
        $schema = $this->environment === 'dev' ? "http://" : "https://";

        $updateUsername = $data['username'] === true;
        $avatarContents = isset($data['avatar_contents']) ? $data['avatar_contents'] : null;
        $mxcAvatar = isset($data['avatar_mxc']) ? $data['avatar_mxc'] : null;

        if(!$updateUsername && is_null($avatarContents) && is_null($mxcAvatar)){
            throw new \Exception("Either username or avatar_contents or avatar_mxc must be set in data.");
        }

        $json = [];
        if(!is_null($avatarContents)){
            $json['avatar_url'] = $this->uploadAvatar($avatarContents);

            if(!$json['avatar_url']){
                return false;
            }
        }

        if(!is_null($mxcAvatar)){
            $json['avatar_url'] = $mxcAvatar;
        }

        if($updateUsername){
            $json['displayname'] = $user->getInfo()->getUsername();
        }

        $client = new Client();
        $res = $client->request('PUT', $schema . $matrixServer . "/_synapse/admin/v2/users/@tb_" . $user->getId() . ":" . $matrixServer, [
            'http_errors' => false,
            'allow_redirects' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAdminAccessToken(),
            ],
            'json' => $json
        ]);

        if(json_last_error() === JSON_ERROR_NONE && isset($result['errcode']) && $result['errcode'] === "M_UNKNOWN_TOKEN"){
            $this->refreshAdminAccessToken();
        }

        if($res->getStatusCode() >= 200 && $res->getStatusCode() < 204){
            return true;
        }

        return false;
    }

    public function getUserTokenAndDevice(string $userId): array
    {
        return $this->generateTokenAndDevice($userId);
    }

    public function listUserIds(int $page): array
    {
        $matrixServer = $this->matrixServer;
        $schema = $this->environment === 'dev' ? "http://" : "https://";

        $client = new Client();
        $res = $client->request('GET', $schema . $matrixServer . "/_synapse/admin/v2/users", [
            'http_errors' => false,
            'allow_redirects' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAdminAccessToken(),
            ],
            'query' => [
                'from' => $page * 500,
                'limit' => 500,
                'guests' => "false",
            ]
        ]);

        $result = json_decode($res->getBody(), true);

        if(json_last_error() === JSON_ERROR_NONE && isset($result['errcode']) && $result['errcode'] === "M_UNKNOWN_TOKEN"){
            $this->refreshAdminAccessToken();
        }

        // Invalid result
        if(json_last_error() !== JSON_ERROR_NONE || !isset($result['users'])){
            throw new \Exception("Users are not found.");
        }

        $end = false;
        if(!isset($result['next_token'])){
            $end = true;
        }

        $users = $result['users'];

        $names = array_column($users, 'name');
        $userIds = [];

        foreach($names as $name){
            $arr = explode(":", $name);

            if(count($arr) === 0){
                continue;
            }

            $userIds[] = substr($arr[0], 4);
        }

        return [
          'end' => $end,
          'users' => $userIds,
        ];
    }

    public function uploadAvatar(string $avatarContents): string
    {
        $matrixServer = $this->matrixServer;
        $schema = $this->environment === 'dev' ? "http://" : "https://";

        $client = new Client();
        $res = $client->request('POST', $schema . $matrixServer . "/_matrix/media/r0/upload", [
            'http_errors' => false,
            'allow_redirects' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->getAdminAccessToken(),
                'Content-Type' => 'image/png',
            ],
            'query' => [
                'filename' => "avatar.png"
            ],
            'body' => $avatarContents,
        ]);

        $result = json_decode($res->getBody()->getContents(), true);

        if(json_last_error() === JSON_ERROR_NONE && isset($result['errcode']) && $result['errcode'] === "M_UNKNOWN_TOKEN"){
            $this->refreshAdminAccessToken();
        }

        if(json_last_error() !== JSON_ERROR_NONE || !isset($result['content_uri'])){
            return false;
        }

        return $result['content_uri'];
    }

    /**
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function getAdminAccessToken(): string
    {
        $cache = $this->cache->getItem( 'mtrxakey');
        if(!$cache->isHit()){
            $this->refreshAdminAccessToken();
            $cache = $this->cache->getItem( 'mtrxakey');
        }

        return $cache->get();
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function refreshAdminAccessToken(): void
    {
            $data = $this->generateTokenAndDevice(Snowflake::createSystemSnowflake());

            $cacheValue = $this->cache->getItem( 'mtrxakey');
            $cacheValue->set($data['access_token']);
            $this->cache->save($cacheValue);

    }


    /**
     * Return access token
     *
     * @param string $userId
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function generateTokenAndDevice(string $userId): array
    {
        $matrixServer = $this->matrixServer;
        $authRedirect = $this->redirectURL;
        $schema = $this->environment === 'dev' ? "http://" : "https://";

        $client = new Client(['cookies' => true]);
        $res = $client->request('GET', $schema . $matrixServer . "/_matrix/client/r0/login/sso/redirect?redirectUrl=" . urlencode($authRedirect), [
            'http_errors' => false,
            'allow_redirects' => false,
        ]);

        $cookieJar = $client->getConfig('cookies');
        $cookies = $cookieJar->toArray();

        if(count($cookies) === 0){
            throw new \Exception("Matrix SSO not returning cookies.");
        }

        $cookieJar = new CookieJar(false, $cookies);

        $location = $res->getHeader('Location');

        if(count($location) === 0){
            throw new \Exception("Matrix SSO not returning location redirect.");
        }

        $location = $location[0];
        parse_str(parse_url($location, PHP_URL_QUERY), $queryArray);

        if(!isset($queryArray['response_type']) || $queryArray['response_type'] !== "code"){
            throw new \Exception("Invalid response_type, must be type code.");
        }

        $application = $this->em->getRepository(Application::class)
            ->findApplication($queryArray['client_id']);

        if(is_null($application)){
            throw new \Exception("Client ID is invalid.");
        }

        if(!in_array($queryArray['redirect_uri'], array_column($application['redirect_urls'], "url"))){
            throw new \Exception("Redirect (${$queryArray['redirect_uri']}) URI is not valid.");
        }

        $code = new Code();
        $code->setClientId($application['id']);

        if(isset($queryArray['state'])){
            $code->setState($queryArray['state']);
        }

        if($queryArray['scope'] !== Code::$SCOPE_READ_USER){
            throw new \Exception("Scope is invalid. Must be read:user.");
        }

        $code->setUserId($userId);
        $code->addScope($queryArray['scope']);

        $this->em->persist($code);
        $this->em->flush();

        $res = $client->request('GET', $schema . $matrixServer . "/_synapse/client/oidc/callback", [
            'http_errors' => false,
            'allow_redirects' => false,
            'cookies' => $cookieJar,
            'query' => [
                'state' => $code->getState(),
                'code' => $code->getCode(),
            ]
        ]);

        $dom = new \DOMDocument;
        @$dom->loadHTML($res->getBody());
        $links = $dom->getElementsByTagName('a');

        $loginToken = null;
        foreach ($links as $link){
            $href = $link->getAttribute('href');
            if(str_contains($href, "loginToken")){
                $parts = parse_url($href);
                parse_str($parts['query'], $query);

                if(!isset($query['loginToken'])) continue;

                $loginToken = $query['loginToken'];
                break;
            }
        }

        if(is_null($loginToken)){
            throw new \Exception("Login token not found.");
        }

        $res = $client->request('POST', $schema . $matrixServer . "/_matrix/client/r0/login", [
            'http_errors' => false,
            'allow_redirects' => false,
            'cookies' => $cookieJar,
            'json' => [
                'token' => $loginToken,
                'type' => "m.login.token"
            ]
        ]);

        $result = json_decode($res->getBody(), true);

        // Invalid result
        if(json_last_error() !== JSON_ERROR_NONE || !isset($result['access_token'])){
            throw new \Exception("Access token not found.");
        }

        if(!isset($result['device_id'])){
            throw new \Exception("Device ID not found.");
        }

        return [
            'access_token' => $result['access_token'],
            'device_id' => $result['device_id'],
        ];
    }

}