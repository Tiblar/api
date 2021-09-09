<?php
namespace App\Entity\Video;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     indexes={
 *      @ORM\Index(name="history_idx", columns={"user_id"}),
 *     },
 *     name="video_history"
 * )
 */
class VideoHistory
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private string $id;

    /**
     * Person who is viewed
     *
     * @ORM\Column(type="string")
     */
    private string $userId;

    /**
     * Post that is viewed
     *
     * @ORM\Column(type="string")
     */
    private string $postId;

    /**
     * Video that is watched previously
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $lastId;

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
    public function getLastId(): string
    {
        return $this->lastId;
    }

    /**
     * @param string|null $lastId
     */
    public function setLastId(?string $lastId): void
    {
        $this->lastId = $lastId;
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
            'timestamp' => $this->getTimestamp()->format('c')
        ];
    }
}
