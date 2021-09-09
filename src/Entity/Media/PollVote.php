<?php
namespace App\Entity\Media;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="poll_vote_idx", columns={"post_id"}),
 *     @ORM\Index(name="poll_vote_user_idx", columns={"user_id"}),
 * })
 */
class PollVote
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
     * @ORM\Column(type="integer")
     */
    private $choice;

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
     * @param string $postId
     */
    public function setPostId(string $postId): void
    {
        $this->postId = $postId;
    }

    /**
     * @return string
     */
    public function getPostId(): string
    {
        return $this->postId;
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
     * @return int
     */
    public function getChoice(): int
    {
        return $this->choice;
    }

    /**
     * @param int $choice
     */
    public function setChoice(int $choice): void
    {
        $this->choice = $choice;
    }

    /**
     * @param \DateTime $timestamp
     */
    public function setTimestamp(\DateTime $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'post_id' => $this->getPostId(),
            'option' => $this->getChoice(),
        ];
    }
}
