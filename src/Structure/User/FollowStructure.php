<?php
namespace App\Structure\User;

class FollowStructure {

    /**
     * @var array
     */
    private $following;

    /**
     * @var array
     */
    private $followers;

    /**
     * @var array
     */
    private $requests;

    public function __construct(array $following, array $followers, array $requests)
    {
        $this->following = $following;
        $this->followers = $followers;
        $this->requests = $requests;
    }

    public function getFollowing()
    {
        return $this->following;
    }

    public function getFollowers()
    {
        return $this->followers;
    }

    public function getRequests()
    {
        return $this->requests;
    }
}