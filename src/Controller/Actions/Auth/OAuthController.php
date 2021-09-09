<?php
namespace App\Controller\Actions\Auth;

use App\Entity\Application\Application;
use App\Entity\Application\OAuth\AccessToken;
use App\Entity\Application\OAuth\Code;
use App\Entity\Application\OAuth\RefreshToken;
use App\Entity\User\User;
use App\Service\Generator\Snowflake;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class OAuthController extends AbstractController
{
    /**
     * @Route("/oauth/token", name="oauth_token", methods={"POST"})
     */
    public function token(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $code = $request->request->get("code");
        $clientId = $request->request->get("client_id");
        $clientSecret = $request->request->get("client_secret");
        $grantType = $request->request->get("grant_type");
        $scope = $request->request->get("scope");

        if(!$request->request->has('client_id') && !$request->request->has('client_secret')){
            $clientId = $request->headers->get('php-auth-user');
            $clientSecret = $request->headers->get('php-auth-pw');
        }

        $authCode = $em->getRepository(Code::class)->findOneBy([
            'clientId' => $clientId,
            'code' => $code,
        ]);

        if(!($authCode instanceof Code)){
            return new JsonResponse([
                'error' => 'Invalid code.'
            ], 400);
        }

        if(isset($scopes) && !empty($scopes) && !is_null($scopes) && is_string($scopes)){
            $scopes = explode(" ", $scope);
            foreach($scopes as $scope){
                if(!in_array($scope, $authCode->getScopes())){
                    return new JsonResponse([
                        'error' => 'Unauthorized scope.'
                    ], 400);
                }
            }
        }else{
            $scopes = $authCode->getScopes();
        }

        $application = $em->getRepository(Application::class)->findOneBy([
            'id' => $clientId,
            'clientSecret' => $clientSecret,
        ]);

        if(!($application instanceof Application)){
            return new JsonResponse([
                'error' => 'Invalid client secret.'
            ], 400);
        }

        if($grantType !== "authorization_code"){
            return new JsonResponse([
                'error' => 'Invalid grant type.'
            ], 400);
        }

        $accessToken = new AccessToken();
        $refreshAccessToken = new AccessToken();

        foreach($scopes as $scope) {
            $accessToken->addScope($scope);
            $refreshAccessToken->addScope($scope);
        }

        $accessToken->setClientId($clientId);
        $accessToken->setUserId($authCode->getUserId());
        $em->persist($accessToken);

        $refreshAccessToken->setClientId($clientId);
        $refreshAccessToken->setUserId($authCode->getUserId());
        $em->persist($refreshAccessToken);

        $refreshToken = new RefreshToken();
        $refreshToken->setClientId($clientId);
        $refreshToken->setToken($refreshAccessToken);
        $em->persist($refreshToken);

        $em->flush();

        return new JsonResponse([
            'token_type' => 'bearer',
            'access_token' => $accessToken->getToken(),
            'refresh_token' => $refreshToken->getToken()->getToken(),
            'expires_in' => (int) ($accessToken->getExpireTimestamp()->getTimestamp() - $accessToken->getTimestamp()->getTimestamp()),
            'scope' => $scope,
        ]);
    }

    /**
     * @Route("/oauth/identity", name="oauth_identity", methods={"GET"})
     */
    public function identity(Request $request, LoggerInterface $logger)
    {
        $em = $this->getDoctrine()->getManager();

        $authorization = $request->headers->get("authorization");

        if(substr($authorization, 0, 7) !== "Bearer "){
            return new JsonResponse([
                'error' => 'Authorization invalid.'
            ], 401);
        }

        $parts = explode(" ", $authorization);

        if(count($parts) !== 2){
            return new JsonResponse([
                'error' => 'Authorization invalid.'
            ], 401);
        }

        $token = $parts[1];

        $authToken = $em->getRepository(AccessToken::class)->findOneBy([
            'token' => $token,
        ]);

        $refreshToken = $em->getRepository(RefreshToken::class)->findOneBy([
            'accessToken' => $authToken,
        ]);

        if(!($authToken instanceof AccessToken) || $refreshToken instanceof RefreshToken){
            return new JsonResponse([
                'error' => 'Authorization invalid.'
            ], 401);
        }

        if($authToken->isExpired() || $authToken->isRevoked()){
            return new JsonResponse([
                'error' => 'Authorization invalid.'
            ], 401);
        }

        $userData = [];

        if($authToken->getUserId() === Snowflake::createSystemSnowflake()){
            $userData = [
                "id" => $authToken->getUserId(),
                "email" => "support@formerlychucks.net",
                "info" => [
                    "username" => "Formerly Chuck's Admin",
                ],
            ];
        }

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $authToken->getUserId(),
        ]);

        if(!($user instanceof User) && $authToken->getUserId() !== Snowflake::createSystemSnowflake()){
            return new JsonResponse([
                'error' => 'Invalid user.'
            ], 400);
        }

        if($user instanceof User && in_array(Code::$SCOPE_READ_USER, $authToken->getScopes())){
            $userData = [
                "id" => $user->getId(),
                "email" => $user->getEmail(),
                "info" => [
                    "username" => $user->getInfo()->getUsername(),
                ]
            ];
        }

        return new JsonResponse($userData);
    }

    /**
     * @Route("/oauth/auth", name="oauth_auth", methods={"GET"})
     */
    public function getAuthToken(Request $request, AuthorizationCheckerInterface $authorizationChecker)
    {
        $em = $this->getDoctrine()->getManager();

        if(!$authorizationChecker->isGranted("ROLE_USER")){

            $urlparts = parse_url($request->getUri());

            $extracted = null;
            if(isset($urlparts['path'])){
                $extracted = $urlparts['path'];
            }

            if(isset($urlparts['path']) && isset($urlparts['query'])){
                $extracted .= '?' . $urlparts['query'];
            }

            if(isset($urlparts['path']) && isset($urlparts['fragment'])){
                $extracted .= '#' . $urlparts['fragment'];
            }

            $query = "";
            if(!is_null($extracted)){
                $query = "?to=" . $extracted;
            }

            return $this->redirect("/login" . $query);
        }

        $application = $em->getRepository(Application::class)
            ->findApplication($request->query->get('client_id'));

        if(is_null($application)){
            return new JsonResponse([
                'error' => 'Client ID is invalid.'
            ], 400);
        }

        if(!in_array($request->query->get('redirect_uri'), array_column($application['redirect_urls'], "url"))){
            return new JsonResponse([
                'error' => 'Redirect URI is not valid.'
            ], 400);
        }

        $code = new Code();
        $code->setClientId($application['id']);

        if($request->query->has('state')){
            $code->setState($request->query->get('state'));
        }

        if($request->query->get('scope') !== Code::$SCOPE_READ_USER){
            return new JsonResponse([
                'error' => 'Scope is invalid. Must be read:user.'
            ], 400);
        }

        $code->setUserId($this->getUser()->getId());
        $code->addScope($request->query->get('scope'));

        $em->persist($code);
        $em->flush();

        $redirectURI = $request->query->get('redirect_uri');
        $query = parse_url($redirectURI, PHP_URL_QUERY);

        if($query){
            $redirectURI .= '&code=' . $code->getCode();
        }else{
            $redirectURI .= '?code=' . $code->getCode();
        }

        if($code->getState()){
            $redirectURI .= '&state=' . $code->getState();
        }

        return $this->redirect($redirectURI);
    }
}
