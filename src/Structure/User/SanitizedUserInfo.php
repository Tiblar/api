<?php
namespace App\Structure\User;

use App\Entity\User\UserInfo;

use App\Structure\Media\SanitizedFile;

/**
 * User Info without sensitive information.
 */
class SanitizedUserInfo
{
    /**
     * @var string
     */
    private $infoId;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $usernameColor;

    /**
     * @var \DateTime
     */
    private $joinDate;

    /**
     * @var SanitizedFile
     */
    private $avatar;

    /**
     * @var string
     */
    private $banner;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var bool
     */
    private $nsfw;

    /**
     * Bio
     *
     * @var string
     */
    private $biography;

    /**
     * @var int
     */
    private $followerCount;

    /**
     * @var string
     */
    private $location;

    /**
     * @var ?string
     */
    private $profileTheme;

    /**
     * @var string
     */
    private $status;

    /**
     * @var bool
     */
    private $following;

    /**
     * @var bool
     */
    private $followedBy;

    /**
     * @var bool
     */
    private $followRequested;

    /**
     * @var bool
     */
    private $blocking;

    /**
     * @var bool
     */
    private $blockedBy;

    public function __construct(UserInfo $info, ?SanitizedPrivacy $privacy)
    {
        $this->infoId = $info->getId();
        $this->username = $info->getUsername();
        $this->usernameColor = $info->getUsernameColor();
        $this->joinDate = $info->getJoinDate();
        $this->avatar = new SanitizedFile($info->getAvatar()->toArray());
        $this->banner = $info->getBanner();
        $this->locale = $info->getLocale();
        $this->nsfw = $info->isNsfw();
        $this->biography = $info->getBiography();
        if(is_null($privacy) || $privacy->getFollowerCount()){
            $this->followerCount = $info->getFollowerCount();
        }
        $this->location = $info->getLocation();
        $this->profileTheme = $info->getProfileTheme();
        $this->status = $info->getStatus();
    }

    /**
     * @return string
     */
    public function getInfoId(): string
    {
        return $this->infoId;
    }

    /**
     * @param string $infoId
     */
    public function setInfoId(string $infoId): void
    {
        $this->infoId = $infoId;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string|null
     */
    public function getUsernameColor()
    {
        return $this->usernameColor;
    }

    /**
     * @param string|null $usernameColor
     */
    public function setUsernameColor($usernameColor): void
    {
        $this->usernameColor = $usernameColor;
    }

    /**
     * @return \DateTime
     */
    public function getJoinDate(): \DateTime
    {
        return $this->joinDate;
    }

    /**
     * @param \DateTime $joinDate
     */
    public function setJoinDate(\DateTime $joinDate): void
    {
        $this->joinDate = $joinDate;
    }

    /**
     * @return SanitizedFile
     */
    public function getAvatar(): SanitizedFile
    {
        return $this->avatar;
    }

    /**
     * @param SanitizedFile $avatar
     */
    public function setAvatar(SanitizedFile $avatar): void
    {
        $this->avatar = $avatar;
    }


    /**
     * @return string|null
     */
    public function getBanner(): ?string
    {
        return $this->banner;
    }

    /**
     * @param string|null $banner
     */
    public function setBanner(?string $banner): void
    {
        $this->banner = $banner;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return bool
     */
    public function isNsfw(): bool
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
     * @return string|null
     */
    public function getBiography()
    {
        return $this->biography;
    }

    /**
     * @param string|null $biography
     */
    public function setBiography($biography): void
    {
        $this->biography = $biography;
    }

    /**
     * @return int
     */
    public function getFollowerCount(): ?int
    {
        return $this->followerCount;
    }

    /**
     * @param int $followerCount
     */
    public function setFollowerCount(int $followerCount): void
    {
        $this->followerCount = $followerCount;
    }

    /**
     * @return string|null
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string|null $location
     */
    public function setLocation($location): void
    {
        $this->location = $location;
    }

    /**
     * @return string|null
     */
    public function getProfileTheme()
    {
        return $this->profileTheme;
    }

    /**
     * @param string|null $profileTheme
     */
    public function setProfileTheme($profileTheme): void
    {
        $this->profileTheme = $profileTheme;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return bool|null
     */
    public function isFollowing(): ?bool
    {
        return $this->following;
    }

    /**
     * @param bool $following
     */
    public function setFollowing(bool $following): void
    {
        $this->following = $following;
    }

    /**
     * @return bool|null
     */
    public function isFollowedBy(): ?bool
    {
        return $this->followedBy;
    }

    /**
     * @param bool $followedBy
     */
    public function setFollowedBy(bool $followedBy): void
    {
        $this->followedBy = $followedBy;
    }

    /**
     * @return bool|null
     */
    public function isFollowRequested(): ?bool
    {
        return $this->followRequested;
    }

    /**
     * @param bool $followRequested
     */
    public function setFollowRequested(bool $followRequested): void
    {
        $this->followRequested = $followRequested;
    }

    /**
     * @return bool|null
     */
    public function isBlocking(): ?bool
    {
        return $this->blocking;
    }

    /**
     * @param bool $blocking
     */
    public function setBlocking(bool $blocking): void
    {
        $this->blocking = $blocking;
    }

    /**
     * @return bool|null
     */
    public function isBlockedBy(): ?bool
    {
        return $this->blockedBy;
    }

    /**
     * @param bool $blockedBy
     */
    public function setBlockedBy(bool $blockedBy): void
    {
        $this->blockedBy = $blockedBy;
    }

    public function toArray(): array
    {
        return [
            'id'=> $this->getInfoId(),
            'username' => $this->getUsername(),
            'username_color' => $this->getUsernameColor(),
            'join_date' => $this->joinDate->format('c'),
            'avatar' => $this->getAvatar()->getURL(),
            'banner' => $this->getBanner(),
            'locale' => $this->getLocale(),
            'nsfw' => $this->isNsfw(),
            'biography' => $this->getBiography(),
            'follower_count' => $this->getFollowerCount(),
            'location' => $this->getLocation(),
            'profile_theme' => $this->getProfileTheme(),
            'status' => $this->getStatus(),
            'following' => $this->isFollowing(),
            'followed_by' => $this->isFollowedBy(),
            'follow_requested' => $this->isFollowRequested(),
            'blocking' => $this->isBlocking(),
            'blocked_by' => $this->isBlockedBy(),
        ];
    }
}