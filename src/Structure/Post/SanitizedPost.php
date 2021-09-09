<?php
namespace App\Structure\Post;

use App\Structure\Media\SanitizedAttachment;
use App\Structure\Media\SanitizedMagnet;
use App\Structure\Media\SanitizedPoll;
use App\Structure\User\SanitizedUser;

class SanitizedPost
{

    private $id;

    private $author;

    private $title;

    private $body;

    private $views;

    private $favoritesCount;

    private $reblogsCount;

    private $repliesCount;

    private $reblog;

    private $reblogged = false;

    private $favorited = false;

    private $pinned = false;

    private $nsfw;

    private $private;

    private $spam;

    private $followersOnly;

    private $timestamp;

    private $updatedTimestamp;

    private $attachments = [];

    private $poll;

    private $magnet;

    private $mentions = [];

    private $tags = [];

    private $videoCategories = [];

    public function __construct(array $arr)
    {
        $this->views = 0;

        if(isset($arr['id']) && is_string($arr['id'])){
            $this->setId($arr['id']);
        }

        if(isset($arr['author']) && $arr['author'] INSTANCEOF SanitizedUser){
            $this->setAuthor($arr['author']);
        }

        if(isset($arr['title'])){
            $this->setTitle($arr['title']);
        }

        if(isset($arr['body'])){
            $this->setBody($arr['body']);
        }

        if(isset($arr['body'])){
            $this->setBody($arr['body']);
        }

        if(isset($arr['favoritesCount']) && ctype_digit($arr['favoritesCount'])){
            $this->setFavoritesCount(intval($arr['favoritesCount']));
        }

        if(isset($arr['reblogsCount']) && ctype_digit($arr['reblogsCount'])){
            $this->setReblogsCount(intval($arr['reblogsCount']));
        }

        if(isset($arr['repliesCount']) && ctype_digit($arr['repliesCount'])){
            $this->setRepliesCount(intval($arr['repliesCount']));
        }

        if(isset($arr['reblog']) && $arr['reblog'] INSTANCEOF SanitizedPost){
            $this->setReblog($arr['reblog']);
        }

        if(isset($arr['nsfw']) && is_bool($arr['nsfw'])){
            $this->setNsfw($arr['nsfw']);
        }

        if(isset($arr['private']) && is_bool($arr['private'])){
            $this->setPrivate($arr['private']);
        }

        if(isset($arr['followersOnly']) && is_bool($arr['followersOnly'])){
            $this->setFollowersOnly($arr['followersOnly']);
        }

        if(isset($arr['videoCategory']) && is_array($arr['videoCategory'])){
            $this->setVideoCategory($arr['videoCategory']);
        }

        if(isset($arr['timestamp']) && $arr['timestamp'] INSTANCEOF \DateTime){
            $this->setTimestamp($arr['timestamp']);
        }

        if(isset($arr['updatedTimestamp']) && $arr['updatedTimestamp'] INSTANCEOF \DateTime){
            $this->setUpdatedTimestamp($arr['updatedTimestamp']);
        }

        if(isset($arr['attachments']) && is_array($arr['attachments'])){
            foreach($arr['attachments'] as $attachment){
                if($attachment INSTANCEOF SanitizedAttachment){
                    $this->addAttachment($attachment);
                }

                if(is_array($attachment)){
                    $sanitized = new SanitizedAttachment($attachment);
                    $sanitized->setPostId($this->getId());

                    $this->addAttachment($sanitized);
                }
            }
        }

        if(isset($arr['poll']) && $arr['poll'] INSTANCEOF SanitizedPoll){
            $this->setPoll($arr['poll']);
        }

        if(isset($arr['tags']) && is_array($arr['tags'])){
            $this->setTags($arr['tags']);
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
     * @return SanitizedUser
     */
    public function getAuthor(): SanitizedUser
    {
        return $this->author;
    }

    /**
     * @param SanitizedUser $author
     */
    public function setAuthor(SanitizedUser $author): void
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
    public function setTitle(?string $title): void
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
     * @param int $views
     */
    public function setViews(int $views): void
    {
        $this->views = $views;
    }

    /**
     * @return int
     */
    public function getViews(): int
    {
        return $this->views;
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
     * @return SanitizedPost|null
     */
    public function getReblog(): ?SanitizedPost
    {
        return $this->reblog;
    }

    /**
     * @param SanitizedPost|null $reblog
     */
    public function setReblog(?SanitizedPost $reblog): void
    {
        $this->reblog = $reblog;
    }

    /**
     * @return bool
     */
    public function isReblogged()
    {
        return $this->reblogged;
    }

    /**
     * @param bool $nsfw
     */
    public function setReblogged(bool $reblogged): void
    {
        $this->reblogged = $reblogged;
    }

    /**
     * @return bool
     */
    public function isFavorited()
    {
        return $this->favorited;
    }

    /**
     * @param bool $favorited
     */
    public function setFavorited(bool $favorited): void
    {
        $this->favorited = $favorited;
    }

    /**
     * @return bool
     */
    public function isPinned()
    {
        return $this->pinned;
    }

    /**
     * @param bool $pinned
     */
    public function setPinned(bool $pinned): void
    {
        $this->pinned = $pinned;
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
     * @return bool|null
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
     * @return \array
     */
    public function getVideoCategory(): array
    {
        return $this->videoCategories;
    }

    /**
     * @param \DateTime $categories
     */
    public function setVideoCategory(array $categories): void
    {
        $this->videoCategories = $categories;
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
     * @return array
     */
    public function getAttachments(): array
    {
        $attachments = [];
        foreach($this->attachments as $attachment){
            $attachments[] = $attachment->toArray();
        }

        return $attachments;
    }

    /**
     * @param SanitizedAttachment $attachment
     */
    public function addAttachment(SanitizedAttachment $attachment): void
    {
        $this->attachments[] = $attachment;
    }

    /**
     * @return SanitizedPoll|null
     */
    public function getPoll(): ?SanitizedPoll
    {
        return $this->poll;
    }

    /**
     * @param SanitizedPoll|null $poll
     */
    public function setPoll(?SanitizedPoll $poll): void
    {
        $this->poll = $poll;
    }

    /**
     * @return SanitizedMagnet|null
     */
    public function getMagnet(): ?SanitizedMagnet
    {
        return $this->magnet;
    }

    /**
     * @param SanitizedMagnet $magnet
     */
    public function setMagnet(SanitizedMagnet $magnet): void
    {
        $this->magnet = $magnet;
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
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array $tag
     */
    public function addTag(array $tag): void
    {
        $this->tags[] = $tag;
    }

    /**
     * @param array $tags
     */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        $reblog = null;
        if($this->getReblog() INSTANCEOF SanitizedPost){
            $reblog = $this->getReblog()->toArray();
        }

        $poll = null;
        if($this->getPoll() INSTANCEOF SanitizedPoll){
            $poll = $this->getPoll()->toArray();
        }

        $magnet = null;
        if($this->getMagnet() INSTANCEOF SanitizedMagnet){
            $magnet = $this->getMagnet()->toArray();
        }

        $updatedTimestamp = null;
        if($this->getReblog() INSTANCEOF \DateTime){
            $updatedTimestamp = $this->getUpdatedTimestamp()->format('c');
        }

        $arr = [
            'id' => $this->getId(),
            'author' => $this->getAuthor()->toArray(),
            'title' => $this->getTitle(),
            'body' => $this->getBody(),
            'views' => $this->getViews(),
            'reblogs_count' => $this->getReblogsCount(),
            'favorites_count' => $this->getFavoritesCount(),
            'replies_count' => $this->getRepliesCount(),
            'reblog' => $reblog,
            'is_reblogged' => $this->isReblogged(),
            'is_favorited' => $this->isFavorited(),
            'pinned' => $this->isPinned(),
            'nsfw' => $this->isNsfw(),
            'private' => $this->isPrivate(),
            'followers_only' => $this->isFollowersOnly(),
            'timestamp' => $this->getTimestamp()->format('c'),
            'updated_timestamp' => $updatedTimestamp,
            'attachments' => $this->getAttachments(),
            'poll' => $poll,
            'magnet' => $magnet,
            'mentions' => $this->getMentions(),
            'tags' => $this->getTags(),
            'video_categories' => $this->getVideoCategory(),
        ];

        if(!is_null($this->isSpam())){
            $arr['spam'] = $this->isSpam();
        }

        return $arr;
    }
}