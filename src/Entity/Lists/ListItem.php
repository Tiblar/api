<?php
namespace App\Entity\Lists;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="post_list_item", indexes={
 *     @ORM\Index(name="post_list_item_idx", columns={"id", "user_id"}),
 *     @ORM\Index(name="post_list_item_post_idx", columns={"post_id"}),
 *     @ORM\Index(name="post_list_item_user_idx", columns={"id", "user_id"})
 * })
 */
class ListItem
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private $id;

    /**
     * Who added the post to the list
     *
     * @ORM\Column(type="string")
     */
    private $userId;

    /**
     * @ORM\Column(type="string")
     */
    private $listId;

    /**
     * @ORM\Column(type="string")
     */
    private $postId;

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
    public function getListId(): string
    {
        return $this->listId;
    }

    /**
     * @param string $listId
     */
    public function setListId(string $listId): void
    {
        $this->listId = $listId;
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
}