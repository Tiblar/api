<?php
namespace App\Entity\User\Addons;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Entity\User\UserRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="follow_follower_idx", columns={"follower_id"}),
 *     @ORM\Index(name="follow_followed_idx", columns={"followed_id"}),
 *  },
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="follow_unique", columns={"follower_id", "followed_id"})
 *  })
 */
class Follow
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
    private $followerId;

    /**
     * @ORM\Column(type="string")
     */
    private $followedId;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @ORM\Version
     * @var \DateTime
     */
    private $timestamp = null;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getFollowerId(): string
    {
        return $this->followerId;
    }

    public function setFollowerId(string $followerId)
    {
        $this->followerId = $followerId;
    }

    public function getFollowedId(): string
    {
        return $this->followedId;
    }

    public function setFollowedId(string $followedId)
    {
        $this->followedId = $followedId;
    }

    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    public function toArray(): array
    {
        return [
            'follower_id' => $this->getFollowerId(),
            'followed_id' => $this->getFollowedId(),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}
