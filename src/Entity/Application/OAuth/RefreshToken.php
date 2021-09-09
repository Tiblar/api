<?php
namespace App\Entity\Application\OAuth;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     indexes={
 *         @ORM\Index(name="oauth_refresh_token_idx", columns={"access_token_id"}),
 *     },
 *     name="oauth_refresh_token"
 * )
 */
class RefreshToken
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Application\OAuth\AccessToken", fetch="EAGER")
     * @ORM\JoinColumn(name="access_token_id", referencedColumnName="id")
     */
    private AccessToken $accessToken;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $timestamp;

    public function __construct()
    {
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
     * @return AccessToken
     */
    public function getToken(): AccessToken
    {
        return $this->accessToken;
    }

    /**
     * @param AccessToken $token
     */
    public function setToken(AccessToken $token): void
    {
        $this->accessToken = $token;
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
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}