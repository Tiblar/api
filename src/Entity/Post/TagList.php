<?php
namespace App\Entity\Post;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="tag_list_idx", columns={"post"}),
 *     @ORM\Index(name="tag_list_user_id_idx", columns={"user_id"}),
 *     @ORM\Index(name="tag_list_tag_idx", columns={"tag"}),
 *     @ORM\Index(name="tag_list_title_idx", columns={"title"})
 * })
 */
class TagList
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private $id;

    /**
     * User who created tag
     *
     * @ORM\Column(type="string")
     */
    private $userId;

    /**
     * @ORM\Column(type="string")
     */
    private $post;

    /**
     * title of tag
     *
     * @ORM\Column(type="string")
     */
    private $tag;

    /**
     * title of tag
     *
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

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
    public function getPost(): string
    {
        return $this->post;
    }

    /**
     * @param string $post
     */
    public function setPost(string $post): void
    {
        $this->post = $post;
    }

    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     */
    public function setTag(string $tag): void
    {
        $this->tag = $tag;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
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

    public function __construct()
    {
        $this->timestamp = new \DateTime();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}
