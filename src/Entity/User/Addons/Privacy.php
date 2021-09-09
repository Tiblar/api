<?php
namespace App\Entity\User\Addons;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={@ORM\Index(name="privacy_idx", columns={"user_id", "id"})})
 */
class Privacy
{
    static $VIEW_EVERYONE = 0;
    static $VIEW_FORMERLY_CHUCKS = 1;
    static $VIEW_FOLLOWERS = 2;

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
     * Who can view the profile
     *
     * 0 = everyone
     * 1 = formerly chuck's users
     * 2 = followers
     *
     * @ORM\Column(type="integer", options={"default":"0"})
     */
    private $view = 0;

    /**
     * true = people can see likes
     * false = likes are hidden
     *
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    private $likes = false;

    /**
     * true = people can see following
     * false = following is hidden
     *
     * @ORM\Column(type="boolean", options={"default":"1"})
     */
    private $following = true;

    /**
     * true = people can see follower count
     * false = follower count is hidden
     *
     * @ORM\Column(type="boolean", options={"default":"1"})
     */
    private $followerCount = true;

    /**
     * true = people can ask questions
     * false = asks are hidden
     *
     * @ORM\Column(type="boolean", options={"default":"1"})
     */
    private $asks = true;

    /**
     * true = people can reply to posts
     * false = replies are disabled for people you don't follow
     *
     * @ORM\Column(type="boolean", options={"default":"1"})
     */
    private $reply = true;

    /**
     * true = anyone can send messages
     * false = messages are disabled for people you don't follow
     *
     * @ORM\Column(type="boolean", options={"default":"1"})
     */
    private $message = true;

    /**
     * true = recommend user content from past activity
     * false = do not recommend user content from past activity
     *
     * @ORM\Column(type="boolean", options={"default":"1"})
     */
    private $recommend = true;

    /**
     * true = save video history
     * false = do not save video history
     *
     * @ORM\Column(type="boolean", options={"default":"1"})
     */
    private $videoHistory = true;

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

    public function getView(): int
    {
        return $this->view;
    }

    public function setView(int $view): void
    {
        $this->view = $view;
    }

    public function getLikes(): bool
    {
        return $this->likes;
    }

    public function setLikes(bool $likes): void
    {
        $this->likes = $likes;
    }

    public function getFollowing(): bool
    {
        return $this->following;
    }

    public function setFollowing(bool $following): void
    {
        $this->following = $following;
    }

    public function getFollowerCount(): bool
    {
        return $this->followerCount;
    }

    public function setFollowerCount(bool $followerCount): void
    {
        $this->followerCount = $followerCount;
    }

    public function getAsks(): bool
    {
        return $this->asks;
    }

    public function setAsks(bool $asks): void
    {
        $this->asks = $asks;
    }

    public function getReply(): bool
    {
        return $this->reply;
    }

    public function setReply(bool $reply): void
    {
        $this->reply = $reply;
    }

    public function getMessage(): bool
    {
        return $this->message;
    }

    public function setMessage(bool $message): void
    {
        $this->message = $message;
    }

    public function getRecommend(): bool
    {
        return $this->recommend;
    }

    public function setRecommend(bool $recommend): void
    {
        $this->recommend = $recommend;
    }

    public function getVideoHistory(): bool
    {
        return $this->videoHistory;
    }

    public function setVideoHistory(bool $history): void
    {
        $this->videoHistory = $history;
    }

    public function toArray()
    {
        return [
            'user_id' => $this->getUserId(),
            'view' => $this->getView(),
            'likes' => $this->getLikes(),
            'following' => $this->getFollowing(),
            'follower_count' => $this->getFollowerCount(),
            'asks' => $this->getAsks(),
            'reply' => $this->getReply(),
            'message' => $this->getMessage(),
            'recommend' => $this->getRecommend(),
            'video_history' => $this->getVideoHistory(),
        ];
    }
}