<?php
namespace App\Entity\Post;

use App\Structure\User\SanitizedUser;
use App\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="reply_post_idx", columns={"reply_post_id"}),
 *     @ORM\Index(name="reply_author", columns={"reply_user_id"})
 * })
 */
class Reply
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Post\Post")
     * @ORM\JoinColumn(name="reply_post_id", referencedColumnName="id")
     */
    private $post;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", fetch="EAGER")
     * @ORM\JoinColumn(name="reply_user_id", referencedColumnName="id")
     */
    private $author;

    /**
     * @ORM\Column(type="text")
     */
    private $body;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Post\Reply", inversedBy="children")
     * @ORM\JoinColumn(name="reply_parent_id", referencedColumnName="id")
     */
    private $parent;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Post\Reply", mappedBy="parent", fetch="EAGER", cascade={"remove", "persist"}, orphanRemoval=true
     * )
     * @ORM\JoinColumn(name="post_children_id", referencedColumnName="id")
     */
    private $children;

    /**
     * @ORM\Column(type="integer")
     */
    private $depth = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp = null;

    public function __construct()
    {
        $this->timestamp = new \DateTime();
        $this->children = new ArrayCollection();
        $this->depth = 0;
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
     * @return Post
     */
    public function getPost(): Post
    {
        return $this->post;
    }

    /**
     * @param Post $post
     */
    public function setPost(Post $post): void
    {
        $this->post = $post;
    }

    /**
     * @return SanitizedUser
     */
    public function getAuthor(): ?SanitizedUser
    {
        if(is_null($this->author)) return null;
        return new SanitizedUser($this->author);
    }

    /**
     * @param null|User $author
     */
    public function setAuthor(?User $author): void
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody(?string $body): void
    {
        $this->body = $body;
    }

    /**
     * @return null|Reply
     */
    public function getParent(): ?Reply
    {
        return $this->parent;
    }

    /**
     * @param null|Reply $parent
     */
    public function setParent(?Reply $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return Collection
     */
    public function getReplies(): ?Collection
    {
        return $this->children;
    }

    private function getRepliesArray(): array
    {
        $replies = [];
        foreach($this->getReplies()->toArray() as $reply){
            $replies[] = $reply->toArray();
        }

        return $replies;
    }

    /**
     * @param Reply $reply
     */
    public function addReply(Reply $reply): void
    {
        $this->children->add($reply);
    }

    /**
     * @param Reply[] $replies
     */
    public function setReplies(array $replies): void
    {
        foreach($replies as $reply){
            $this->addReply($reply);
        }
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
        $parentId = null;
        if($this->getParent() INSTANCEOF Reply){
            $parentId = $this->getParent()->getId();
        }

        $author = null;
        if($this->getAuthor() INSTANCEOF SanitizedUser){
            $author = $this->getAuthor()->toArray();
        }
        
       return [
           'id' => $this->getId(),
           'post_id' => $this->getPost()->getId(),
           'author' => $author,
           'body' => $this->getBody(),
           'parent_id' => $parentId,
           'replies' => $this->getRepliesArray(),
           'depth' => $this->getDepth(),
           'timestamp' => $this->getTimestamp()->format('c'),
       ];
    }
}
