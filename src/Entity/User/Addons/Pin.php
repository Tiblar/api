<?php
namespace App\Entity\User\Addons;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Entity\User\UserRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="user_id", columns={"user_id"}),
 *  },
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="user_unique", columns={"user_id"})
 *  })
 */
class Pin
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
     * @ORM\Column(type="datetime", nullable=false)
     * @ORM\Version
     * @var \DateTime
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

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getPostId(): string
    {
        return $this->postId;
    }

    public function setPostId(string $postId): void
    {
        $this->postId = $postId;
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
            'post_id' => $this->getPostId(),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}
