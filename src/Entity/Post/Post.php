<?php
namespace App\Entity\Post;

use App\Entity\Media\Attachment;
use App\Entity\Media\Magnet;
use App\Entity\Media\Poll;
use App\Entity\Video\Category;
use App\Structure\User\SanitizedUser;
use App\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;

/**
 * @ORM\Entity(repositoryClass="App\Entity\Post\PostRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="post_idx", columns={"id"}),
 *     @ORM\Index(name="post_author_idx", columns={"post_user_id"}),
 *     @ORM\Index(name="post_favorites_count", columns={"favorites_count"}),
 *     @ORM\Index(name="post_reblog", columns={"reblogged_post_id"}),
 *     @ORM\Index(name="post_id_reblog", columns={"id", "post_user_id", "reblogged_post_id"}),
 *     @ORM\Index(name="post_author_reblog", columns={"post_user_id", "reblogged_post_id"}),
 * })
 */
class Post
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private $id;

    /**
     * Optional old UUID
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $oldUUID;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", fetch="EAGER")
     * @ORM\JoinColumn(name="post_user_id", referencedColumnName="id")
     */
    private $author;

    /**
     * title of site
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $body;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $favoritesCount = 0;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $reblogsCount = 0;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $repliesCount = 0;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Post\Post", fetch="EAGER")
     * @ORM\JoinColumn(name="reblogged_post_id", referencedColumnName="id")
     */
    private $reblog;

    /**
     * If content is nsfw
     *
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $nsfw;

    /**
     * If is private post
     *
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $private = false;

    /**
     * If is followers only post
     *
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $followersOnly = false;

    /**
     * If content is marked for deletion review
     *
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $markDelete = false;

    /**
     * If content is hidden by staff (ban, etc)
     *
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $hidden = false;

    /**
     * If content is marked by spam filter
     *
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $spam = false;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $ipAddress;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Video\Category", fetch="EAGER")
     * @ORM\JoinColumn(name="video_category_id", referencedColumnName="id")
     */
    private $videoCategory;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedTimestamp;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Media\Attachment", mappedBy="post", fetch="EAGER", cascade={"remove", "persist"}, orphanRemoval=true
     * )
     * @ORM\JoinColumn(name="post_attachment_id", referencedColumnName="id")
     */
    private $attachments;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Media\Poll", fetch="EAGER", cascade={"remove"})
     * @ORM\JoinColumn(name="poll_id", referencedColumnName="id")
     */
    private $poll;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Media\Magnet", fetch="EAGER", cascade={"remove"})
     * @ORM\JoinColumn(name="post_magnet_id", referencedColumnName="id")
     */
    private $magnet;

    public function __construct()
    {
        $this->timestamp = new \DateTime();
        $this->attachments = new ArrayCollection();
        $this->videoCategory = new ArrayCollection();
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
    public function getOldUUID(): string
    {
        return $this->oldUUID;
    }

    /**
     * @param string $oldUUID
     */
    public function setOldUUID($oldUUID): void
    {
        $this->oldUUID = $oldUUID;
    }

    /**
     * @return SanitizedUser
     */
    public function getAuthor(): SanitizedUser
    {
        return new SanitizedUser($this->author);
    }

    /**
     * @param User $author
     */
    public function setAuthor(User $author): void
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
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
     * @return int
     */
    public function getFavoritesCount(): int
    {
        return intval($this->favoritesCount);
    }

    /**
     * @param int $favoritesCount
     */
    public function setFavoritesCount(int $favoritesCount): void
    {
        if($favoritesCount < 0)
            $favoritesCount = 0;

        $this->favoritesCount = $favoritesCount;
    }

    /**
     * @return int
     */
    public function getReblogsCount(): int
    {
        return intval($this->reblogsCount);
    }

    /**
     * @param int $reblogsCount
     */
    public function setReblogsCount(int $reblogsCount): void
    {
        if($reblogsCount < 0)
            $reblogsCount = 0;

        $this->reblogsCount = $reblogsCount;
    }

    /**
     * @return int
     */
    public function getRepliesCount(): int
    {
        return intval($this->repliesCount);
    }

    /**
     * @param int $repliesCount
     */
    public function setRepliesCount(int $repliesCount): void
    {
        if($repliesCount < 0)
            $repliesCount = 0;

        $this->repliesCount = $repliesCount;
    }

    /**
     * @return Post|null
     */
    public function getReblog(): ?Post
    {
        return $this->reblog;
    }

    private function getReblogArray(): ?array
    {
        if(is_null($this->getReblog())) return null;

        return $this->getReblog()->toArray();
    }

    /**
     * @param Post|null $reblog
     */
    public function setReblog(?Post $reblog): void
    {
        $this->reblog = $reblog;
    }

    /**
     * @return bool
     */
    public function isNsfw()
    {
        return $this->nsfw;
    }

    /**
     * @param bool $nsfw
     */
    public function setNsfw(bool $nsfw): void
    {
        $this->nsfw = $nsfw;
    }

    /**
     * @return bool
     */
    public function isPrivate()
    {
        return $this->private;
    }

    /**
     * @param bool $private
     */
    public function setPrivate(bool $private): void
    {
        $this->private = $private;
    }

    /**
     * @return bool
     */
    public function isFollowersOnly(): bool
    {
        return $this->followersOnly;
    }

    /**
     * @param bool $followersOnly
     */
    public function setFollowersOnly(bool $followersOnly): void
    {
        $this->followersOnly = $followersOnly;
    }

    /**
     * @return bool
     */
    public function isMarkDelete()
    {
        return $this->markDelete;
    }

    /**
     * @param bool $markDelete
     */
    public function setMarkDelete(bool $markDelete): void
    {
        $this->markDelete = $markDelete;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     */
    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    /**
     * @return bool
     */
    public function isSpam()
    {
        return $this->spam;
    }

    /**
     * @param bool $spam
     */
    public function setSpam(bool $spam): void
    {
        $this->spam = $spam;
    }

    /**
     * @return string
     */
    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    /**
     * @param string $ipAddress
     */
    public function setIpAddress(?string $ipAddress): void
    {
        $this->ipAddress = $ipAddress;
    }

    public function addCategory(Category $category): void
    {
        $this->videoCategory->add($category);
    }

    public function removeCategory(Category $category): void
    {
        $this->videoCategory->removeElement($category);
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
     * @return \DateTime
     */
    public function getUpdatedTimestamp(): ?\DateTime
    {
        return $this->updatedTimestamp;
    }

    /**
     * @param \DateTime $updatedTimestamp
     */
    public function setUpdatedTimestamp(\DateTime $updatedTimestamp): void
    {
        $this->updatedTimestamp = $updatedTimestamp;
    }

    /**
     * @return Collection
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    private function getAttachmentsArray(): array
    {
        $attachments = [];
        foreach($this->getAttachments()->toArray() as $attachment){
            $attachments[] = $attachment->toArray();
        }

        return $attachments;
    }

    /**
     * @param Attachment $attachment
     */
    public function addAttachment(Attachment $attachment): void
    {
        $this->attachments->add($attachment);
    }

    /**
     * @param Attachment[] $attachments
     */
    public function setAttachments(array $attachments): void
    {
        foreach($attachments as $attachment){
            $this->addAttachment($attachment);
        }
    }

    /**
     * @return Poll|null
     */
    public function getPoll(): ?Poll
    {
        return $this->poll;
    }

    /**
     * @return array|null
     */
    public function getPollArray(): ?array
    {
        $poll = null;
        if($this->getPoll() INSTANCEOF Poll){
            $poll = $this->getPoll()->toArray();
        }

        return $poll;
    }

    /**
     * @param Poll|null $poll
     */
    public function setPoll(?Poll $poll): void
    {
        $this->poll = $poll;
    }

    /**
     * @return Magnet|null
     */
    public function getMagnet(): ?Magnet
    {
        return $this->magnet;
    }

    /**
     * @return array|null
     */
    public function getMagnetArray(): ?array
    {
        $magnet = null;
        if($this->getMagnet() INSTANCEOF Magnet){
            $magnet = $this->getMagnet()->toArray();
        }

        return $magnet;
    }

    /**
     * @param Magnet|null $magnet
     */
    public function setMagnet(?Magnet $magnet): void
    {
        $this->magnet = $magnet;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $categories = [];
        foreach($this->videoCategory->toArray() as $cat){
            $categories[] = [
                'id' => $cat->getId(),
                'title' => $cat->getTitle(),
            ];
        }

        return [
            'id' => $this->getId(),
            'author' => $this->getAuthor()->toArray(),
            'title' => $this->getTitle(),
            'body' => $this->getBody(),
            'reblogs_count' => $this->getReblogsCount(),
            'favorites_count' => $this->getFavoritesCount(),
            'replies_count' => $this->getRepliesCount(),
            'reblog' => $this->getReblogArray(),
            'nsfw' => $this->isNsfw(),
            'followers_only' => $this->isFollowersOnly(),
            'private' => $this->isPrivate(),
            'video_categories' => $categories,
            'timestamp' => $this->getTimestamp()->format('c'),
            'updated_timestamp' => $this->getUpdatedTimestamp(),
            'attachments' => $this->getAttachmentsArray(),
            'poll' => $this->getPollArray(),
            'magnet' => $this->getMagnetArray(),
        ];
    }
}