<?php
declare(strict_types=1);

namespace App\Structure\User;

use App\Entity\User\Addons\Privacy;
use App\Entity\User\User;

/**
 * User without sensitive information.
 */
class SanitizedPrivacy
{
    private $view = null;

    private $likes = null;

    private $following = null;

    private $followerCount = null;

    private $asks = null;

    private $reply = null;

    private $message = null;

    private $videoHistory = null;

    public function __construct(Privacy $privacy)
    {
        $this->view = $privacy->getView();
        $this->likes = $privacy->getLikes();
        $this->following = $privacy->getFollowing();
        $this->followerCount = $privacy->getFollowerCount();
        $this->asks = $privacy->getAsks();
        $this->reply = $privacy->getReply();
        $this->message = $privacy->getMessage();
        $this->videoHistory = $privacy->getVideoHistory();
    }

    function getView()
    {
        return $this->view;
    }

    function getLikes()
    {
        return $this->likes;
    }

    function getFollowing()
    {
        return $this->following;
    }

    function getFollowerCount()
    {
        return $this->followerCount;
    }

    function getAsks()
    {
        return $this->asks;
    }

    function getReply()
    {
        return $this->reply;
    }

    function getMessage()
    {
        return $this->message;
    }

    function getVideoHistory()
    {
        return $this->videoHistory;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'view' => $this->getView(),
            'likes' => $this->getLikes(),
            'following' => $this->getFollowing(),
            'follower_count' => $this->getFollowerCount(),
            'asks' => $this->getAsks(),
            'reply' => $this->getReply(),
            'message' => $this->getMessage(),
            'video_history' => $this->getVideoHistory(),
        ];
    }
}