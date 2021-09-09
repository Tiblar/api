<?php
namespace App\Entity\User\Addons;

use App\Entity\User\User;
use App\Structure\User\SanitizedUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     indexes={
 *     @ORM\Index(name="notification_idx", columns={"user_id"}),
 *     @ORM\Index(name="notification_date_idx", columns={"type", "timestamp"}),
 *     @ORM\Index(name="notification_post_idx", columns={"post_id"})
 * },
 * )
 */
class Notification
{
    static $TYPE_FAVORITE = "TYPE_FAVORITE";
    static $TYPE_FOLLOW = "TYPE_FOLLOW";
    static $TYPE_FOLLOW_REQUEST = "TYPE_FOLLOW_REQUEST";
    static $TYPE_UNFOLLOW = "TYPE_UNFOLLOW";
    static $TYPE_REBLOG = "TYPE_REBLOG";
    static $TYPE_MENTION = "TYPE_MENTION";
    static $TYPE_REPLY_MENTION = "TYPE_REPLY_MENTION";
    static $TYPE_REPLY = "TYPE_REPLY";
    static $TYPE_SYSTEM = "TYPE_SYSTEM";

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
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $postId;

    /**
     * FAVORITE
     * FOLLOW
     * FOLLOW_REQUEST
     * UNFOLLOW
     * REBLOG
     * MENTION
     * REPLY
     * SYSTEM
     *
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * Users who liked/followed/etc

     * @ORM\ManyToMany(
     *     targetEntity="App\Entity\User\User", fetch="LAZY"
     * )
     * @ORM\JoinColumn(name="causers_user_id", referencedColumnName="id")
     */
    private $causers;

    /**
     * @ORM\Column(type="integer")
     */
    private $interactionsCount = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $seen = false;

    /**
     * System message
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $message;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

    public function __construct()
    {
        $this->causers = new ArrayCollection();
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
     * @return string|null
     */
    public function getPostId(): ?string
    {
        return $this->postId;
    }

    /**
     * @param string|null $postId
     */
    public function setPostId(?string $postId): void
    {
        $this->postId = $postId;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @throws \Exception
     */
    public function setType(string $type): void
    {
        if(
            in_array($type, [
                self::$TYPE_FAVORITE, self::$TYPE_FOLLOW, self::$TYPE_FOLLOW_REQUEST, self::$TYPE_UNFOLLOW,
                self::$TYPE_REBLOG, self::$TYPE_MENTION, self::$TYPE_REPLY_MENTION, self::$TYPE_REPLY,
                self::$TYPE_SYSTEM
            ])
        ){
            $this->type = $type;
        }else{
            throw new \Exception("Invalid notification type");
        }
    }

    /**
     * @return array
     */
    public function getCausers(): array
    {
        $causers = [];
        foreach($this->causers as $causer){
            $sanitized = new SanitizedUser($causer);
            $causers[] = $sanitized->toArray();
        }

        return $causers;
    }

    /**
     * @param User $causer
     * @throws \Exception
     */
    public function addCauser(User $causer): void
    {
        if(is_null($this->getUserId())){
            throw new \Exception("User ID must be set before a causer is add to notification entity.");
        }

        if($causer->getId() === $this->getUserId()){
            return;
        }

        if(!$this->causers->contains($causer)){
            $this->causers->add($causer);
        }

        $this->setInteractionsCount($this->getInteractionsCount() + 1);
    }

    /**
     * @param User $causer
     */
    public function removeCauser(User $causer): void
    {
        $this->causers->removeElement($causer);

        $this->setInteractionsCount($this->getInteractionsCount() - 1);
    }

    /**
     * @return mixed
     */
    public function getInteractionsCount(): int
    {
        return $this->interactionsCount;
    }

    /**
     * @param int $interactionsCount
     */
    public function setInteractionsCount(int $interactionsCount): void
    {
        if($interactionsCount < 0){
            $this->interactionsCount = 0;
        }else{
            $this->interactionsCount = $interactionsCount;
        }
    }

    /**
     * @return bool
     */
    public function getSeen(): bool
    {
        return $this->seen;
    }

    /**
     * @param bool $seen
     */
    public function setSeen(bool $seen): void
    {
        $this->seen = $seen;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    /**
     * @param $timestamp
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
          'user_id' => $this->getUserId(),
          'post_id' => $this->getPostId(),
          'type' => $this->getType(),
          'causers' => $this->getCausers(),
          'seen' => $this->getSeen(),
          'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}
