<?php
namespace App\Entity\Post;

use App\Entity\User\User;
use App\Structure\User\SanitizedUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="post_idx", columns={"post_id"}),
 *     @ORM\Index(name="user_idx", columns={"user_id"}),
 * }, name="post_user_mentions")
 */
class Mention
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
    private $postId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $replyId;

    /**
     * @ORM\Column(type="string")
     */
    private $userId;

    /**
     * @ORM\Column(type="string")
     */
    private $causerId;

    /**
     * @ORM\Column(type="json")
     */
    private $indices;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $timestamp = null;

    public function __construct()
    {
        $this->timestamp = new \DateTime();
    }

    public function getId(): string
    {
        return $this->id;
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
    public function getReplyId(): string
    {
        return $this->replyId;
    }

    /**
     * @param string $replyId
     */
    public function setReplyId(string $replyId): void
    {
        $this->replyId = $replyId;
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
    public function getCauserId(): string
    {
        return $this->causerId;
    }

    /**
     * @param string $causerId
     */
    public function setCauserId(string $causerId): void
    {
        $this->causerId = $causerId;
    }

    /**
     * @return array
     */
    public function getIndices(): array
    {
        return json_decode($this->indices);
    }

    /**
     * @param array $indices
     * @throws \Exception
     */
    public function setIndices(array $indices): void
    {
        if(
            !isset($indices[0]) || !isset($indices[1]) ||
            !is_integer($indices[0]) || !is_integer($indices[1])
        ){
            throw new \Exception("Indices must be integers.");
        }

        $this->indices = json_encode($indices);
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
            'post_id' => $this->getPostId(),
            'reply_id' => $this->getReplyId(),
            'user_id' => $this->getUserId(),
            'causer_id' => $this->getCauserId(),
            'indices' => $this->getIndices(),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}
