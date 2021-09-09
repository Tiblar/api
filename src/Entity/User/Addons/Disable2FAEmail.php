<?php
namespace App\Entity\User\Addons;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Entity\User\UserRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="disable_2fa_user_id_idx", columns={"user_id"}),
 *     @ORM\Index(name="disable_2fa_code_idx", columns={"code"}),
 *  },
 *  name="disable_2fa_email")
 */
class Disable2FAEmail
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $userId;

    /**
     * @ORM\Column(type="string")
     */
    private $code;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expireTimestamp;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

    public function __construct()
    {
        $expireTimestamp = new \DateTime();
        $expireTimestamp->modify("+1 day");

        $this->expireTimestamp = $expireTimestamp;
        $this->timestamp = new \DateTime();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId)
    {
        $this->userId = $userId;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code)
    {
        $this->code = $code;
    }

    public function setExpireTimestamp(\DateTime $expireTimestamp)
    {
        $this->expireTimestamp = $expireTimestamp;
    }

    public function getExpireTimestamp(): \DateTime
    {
        return $this->expireTimestamp;
    }

    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'user_id' => $this->getUserId(),
            'code' => $this->getCode(),
            'expire_timestamp' => $this->getExpireTimestamp()->format('c'),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}
