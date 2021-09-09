<?php
namespace App\Entity\Post;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="post_idx", columns={"post_id"}),
 *     @ORM\Index(name="post_favoriter_idx", columns={"post_id", "favoriter"}),
 *     @ORM\Index(name="favoriter_idx", columns={"favoriter"}),
 *     @ORM\Index(name="favorited_idx", columns={"favorited"}),
 *     @ORM\Index(name="timestamp_idx", columns={"timestamp"})
 * })
 */
class Favorite
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private $id;

    /**
     * Id of person who likes
     *
     * @ORM\Column(type="string")
     */
    private $favoriter;

    /**
     * Id of person who liked
     *
     * @ORM\Column(type="string")
     */
    private $favorited;

    /**
     * Id of original post
     *
     * @ORM\Column(type="string")
     */
    private $postId;

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

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getFavoriter(): string
    {
        return $this->favoriter;
    }

    public function setFavoriter(string $favoriter)
    {
        $this->favoriter = $favoriter;
    }

    public function getFavorited(): string
    {
        return $this->favorited;
    }

    public function setFavorited(string $favorited)
    {
        $this->favorited = $favorited;
    }

    public function getPost(): string
    {
        return $this->postId;
    }

    public function setPost(string $postId)
    {
        $this->postId = $postId;
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
          'favoriter' => $this->getFavoriter(),
          'favorited' => $this->getFavorited(),
          'post' => $this->getPost(),
          'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}
