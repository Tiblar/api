<?php
namespace App\Structure\Post;

use App\Structure\User\SanitizedUser;

class SanitizedReply
{

    private $id;

    private $postId;

    private $parentId;

    private $author;

    private $body;

    private $replies = [];

    private $depth = 0;

    private $mentions = [];

    private $timestamp = null;

    public function __construct(array $arr)
    {
        if(isset($arr['id']) && is_string($arr['id'])){
            $this->setId($arr['id']);
        }

        if(isset($arr['author']) && ($arr['author'] INSTANCEOF SanitizedUser || is_null($arr['author']))){
            $this->setAuthor($arr['author']);
        }

        if(isset($arr['body'])){
            $this->setBody($arr['body']);
        }

        if(isset($arr['depth']) && ctype_digit($arr['depth'])){
            $this->setDepth(intval($arr['depth']));
        }

        if(isset($arr['timestamp']) && $arr['timestamp'] INSTANCEOF \DateTime){
            $this->setTimestamp($arr['timestamp']);
        }
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
    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    /**
     * @param string $parentId
     */
    public function setParentId(string $parentId): void
    {
        $this->parentId = $parentId;
    }

    /**
     * @return SanitizedUser|null
     */
    public function getAuthor(): ?SanitizedUser
    {
        return $this->author;
    }

    /**
     * @param SanitizedUser|null $author
     */
    public function setAuthor(?SanitizedUser $author): void
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * @return int
     */
    public function getDepth(): int
    {
        return $this->depth;
    }

    /**
     * @param int $depth
     */
    public function setDepth(int $depth): void
    {
        $this->depth = $depth;
    }

    function getReplies(): array
    {
        return $this->replies;
    }

    function setReplies(array $replies): void
    {
        $this->replies = $replies;
    }

    function addReply(SanitizedReply $reply): void
    {
        $this->replies[] = $reply;
    }

    /**
     * @return array
     */
    public function getMentions(): array
    {
        return $this->mentions;
    }

    /**
     * @param array $mention
     */
    public function addMention(array $mention): void
    {
        $this->mentions[] = $mention;
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
     * @throws \Exception
     */
    public function toArray(): array
    {
        $replies = [];
        foreach($this->getReplies() as $reply){
            $replies[] = $reply->toArray();
        }

        $author = null;
        if($this->getAuthor() INSTANCEOF SanitizedUser){
            $author = $this->getAuthor()->toArray();
        }

        return [
            'id' => $this->getId(),
            'post_id' => $this->getPostId(),
            'parent_id' => $this->getParentId(),
            'author' => $author,
            'body' => $this->getBody(),
            'replies' => $replies,
            'depth' => $this->getDepth(),
            'mentions' => $this->getMentions(),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}