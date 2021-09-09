<?php
namespace App\Entity\Application\OAuth;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     indexes={
 *         @ORM\Index(name="oauth_code_idx", columns={"code", "client_id", "state"}),
 *         @ORM\Index(name="oauth_user_id_idx", columns={"user_id"}),
 *     },
 *     name="oauth_code"
 * )
 */
class Code
{
    static string $SCOPE_READ_USER = "read:user";

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
    private string $code;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $state = null;

    /**
     * @ORM\Column(type="json")
     */
    private string $scopes;

    /**
     * @ORM\Column(type="string")
     */
    private string $userId;

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
        $this->code = \bin2hex(\openssl_random_pseudo_bytes(16));
        $this->expireTimestamp = new \DateTime("+1 hour");
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
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     */
    public function setState(?string $state): void
    {
        $this->state = $state;
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
            self::$SCOPE_READ_USER
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
            'code' => $this->getCode(),
            'state' => $this->getState(),
            'scope' => $this->getScopes(),
            'user_id' => $this->getUserId(),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}