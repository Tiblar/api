<?php
namespace App\Entity\User;

use App\Structure\User\SanitizedUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={@ORM\Index(name="user_action_log_idx", columns={"action", "action_log_user_id"})}, name="user_action_log")
 */
class ActionLog
{
    static $CHANGE_USERNAME = "CHANGE_USERNAME";
    static $CHANGE_EMAIL = "CHANGE_EMAIL";
    static $CHANGE_PASSWORD = "CHANGE_PASSWORD";
    static $RESET_PASSWORD_REQUEST = "RESET_PASSWORD_REQUEST";
    static $RESET_PASSWORD = "RESET_PASSWORD";
    static $RESEND_EMAIL_CONFIRMATION = "RESEND_EMAIL_CONFIRMATION";
    static $TWO_FACTOR_DISABLE_EMAIL_REQUEST = "TWO_FACTOR_DISABLE_EMAIL_REQUEST";
    static $REQUEST_DELETE_ACCOUNT = "REQUEST_DELETE_ACCOUNT";
    static $AUTH_LOGIN = "AUTH_LOGIN";

    static $BACKBLAZE_CREATE_LARGE_FILE = "BACKBLAZE_CREATE_LARGE_FILE";

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
    private $action;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", fetch="EAGER")
     * @ORM\JoinColumn(name="action_log_user_id", referencedColumnName="id")
     */
    private $author;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $metadata;

    /**
     * @ORM\Column(type="string")
     */
    private $ipAddress;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

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
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     * @throws \Exception
     */
    public function setAction(string $action): void
    {
        if(
            in_array($action, [
                self::$AUTH_LOGIN, self::$CHANGE_EMAIL, self::$TWO_FACTOR_DISABLE_EMAIL_REQUEST,
                self::$RESEND_EMAIL_CONFIRMATION, self::$CHANGE_PASSWORD, self::$CHANGE_USERNAME,
                self::$REQUEST_DELETE_ACCOUNT, self::$RESET_PASSWORD_REQUEST, self::$RESET_PASSWORD,
                self::$BACKBLAZE_CREATE_LARGE_FILE
            ])
        ){
            $this->action = $action;
        }else{
            throw new \Exception("Invalid logger action type");
        }
    }

    /**
     * @return SanitizedUser|null
     */
    public function getAuthor(): ?SanitizedUser
    {
        if($this->author INSTANCEOF User){
            return new SanitizedUser($this->author);
        }

        return null;
    }

    /**
     * @param User|null $author
     */
    public function setAuthor(?User $author): void
    {
        $this->author = $author;
    }

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return json_decode($this->metadata);
    }

    /**
     * @param array $metadata
     */
    public function setMetadata(?array $metadata): void
    {
        $this->metadata = json_encode($metadata);
    }

    /**
     * @return string
     */
    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    /**
     * @param string $ipAddress
     */
    public function setIpAddress(string $ipAddress): void
    {
        $this->ipAddress = $ipAddress;
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
          'action' => $this->getAction(),
          'author' => $this->getAuthor(),
          'metadata' => $this->getMetadata(),
          'ip_address' => $this->getIpAddress(),
          'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}