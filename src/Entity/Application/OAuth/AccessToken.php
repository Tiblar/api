<?php
namespace App\Entity\Application\OAuth;

use App\Entity\User\User;
use App\Structure\User\SanitizedUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     indexes={
 *         @ORM\Index(name="oauth_access_token_idx", columns={"token", "client_id", "revoked"}),
 *     },
 *     name="oauth_access_token"
 * )
 */
class AccessToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private string $id;

    /**
     * @ORM\Column(type="string")
     */
    private string $clientId;

    /**
     * @ORM\Column(type="string")
     */
    private string $token;

    /**
     * @ORM\Column(type="json")
     */
    private string $scopes;

    /**
     * @ORM\Column(type="string")
     */
    private string $userId;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $revoked = false;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $expireTimestamp;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $timestamp;

    public function __construct()
    {
        $this->token = \bin2hex(\openssl_random_pseudo_bytes(16));
        $this->expireTimestamp = new \DateTime("+7 days");
        $this->timestamp = new \DateTime();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId): void
    {
        $this->clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * @return bool
     */
    public function isRevoked(): bool
    {
        return $this->revoked;
    }

    /**
     * Revoke token
     */
    public function revoke(): void
    {
        $this->revoked = true;
    }

    /**
     * @return array
     */
    public function getScopes(): array
    {
        return json_decode($this->scopes, true);
    }

    /**
     * @param string $scope
     * @throws \Exception
     */
    public function addScope(string $scope): void
    {
        if(!isset($this->scopes)){
            $this->scopes = json_encode([]);
        }

        if(in_array($scope, [
            Code::$SCOPE_READ_USER
        ])){
            $scopes = json_decode($this->scopes);

            $scopes[] = $scope;

            $this->scopes = json_encode($scopes);
        }else{
            throw new \Exception("Invalid scope.");
        }
    }

    /**
     * @param array $scopes
     */
    public function setScopes(array $scopes): void
    {
        $this->scopes = json_encode($scopes);
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     */
    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        return ($this->getExpireTimestamp() < new \DateTime());
    }

    /**
     * @return \DateTime
     */
    public function getExpireTimestamp(): \DateTime
    {
        return $this->expireTimestamp;
    }

    /**
     * @param \DateTime $expireTimestamp
     */
    public function setExpireTimestamp(\DateTime $expireTimestamp): void
    {
        $this->expireTimestamp = $expireTimestamp;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    /**
     * @param \DateTime $timestamp
     */
    public function setTimestamp(\DateTime $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'client_id' => $this->getClientId(),
            'token' => $this->getToken(),
            'revoked' => $this->isRevoked(),
            'user_id' => $this->getUserId(),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}