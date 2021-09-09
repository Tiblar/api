<?php
namespace App\Entity\Report;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="post_id", columns={"post_id"}),
 *      @ORM\Index(name="user_id", columns={"user_id"})
 * })
 */
class PostReport
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
    private $postId;

    /**
     * @ORM\Column(type="boolean")
     */
    private $accepted = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $rejected = false;

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
    public function setId($id): void
    {
        $this->id = $id;
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
     * @return string
     */
    public function getPostId(): string
    {
        return $this->postId;
    }

    /**
     * @param string $postId
     */
    public function setPostId(string $postId): void
    {
        $this->postId = $postId;
    }

    /**
     * @return string
     */
    public function getAccepted(): string
    {
        return $this->accepted;
    }

    /**
     * @param string $accepted
     */
    public function setAccepted(string $accepted): void
    {
        $this->accepted = $accepted;
    }

    /**
     * @return string
     */
    public function getRejected(): string
    {
        return $this->rejected;
    }

    /**
     * @param string $rejected
     */
    public function setRejected(string $rejected): void
    {
        $this->rejected = $rejected;
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

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'user_id' => $this->getUserId(),
            'post_id' => $this->getPostId(),
            'accepted' => $this->getAccepted(),
            'rejected' => $this->getRejected(),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}
