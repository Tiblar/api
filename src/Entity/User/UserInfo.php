<?php
declare(strict_types=1);

namespace App\Entity\User;

use App\Entity\Media\File;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Entity\User\UserRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="user_info_idx", columns={"id"}),
 *     @ORM\Index(name="user_username_idx", columns={"id", "username"})
 * })
 */
class UserInfo
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $usernameColor;

    /**
     * @ORM\Column(type="datetime")
     */
    private $joinDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Media\File", fetch="EAGER")
     * @ORM\JoinColumn(name="avatar_file_id", referencedColumnName="id")
     */
    private File $avatar;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $banner;

    /**
     * @ORM\Column(type="string")
     */
    private $locale = "en-US";

    /**
     * If user is an adult blog
     *
     * @ORM\Column(type="boolean")
     */
    private $nsfw = false;

    /**
     * Bio
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $biography;

    /**
     * follower count
     *
     * @ORM\Column(type="bigint")
     */
    private $followerCount = 0;

    /**
     * Location
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $location = "The Internet";

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $profileTheme;

    /**
     * @ORM\Column(type="string")
     */
    private $status = "online";


    public function __construct($info)
    {
        $this->username = $info['username'];

        $this->joinDate = new \DateTime();
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
     * @return File
     */
    public function getAvatar(): File
    {
        return $this->avatar;
    }

    /**
     * @param File $avatar
     */
    public function setAvatar(File $avatar): void
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
    public function getBiography(): ?string
    {
        return $this->biography;
    }

    /**
     * @param string|null $biography
     */
    public function setBiography(?string $biography): void
    {
        $this->biography = $biography;
    }

    /**
     * @return int
     */
    public function getFollowerCount(): int
    {
        return intval($this->followerCount);
    }

    /**
     * @param int $followerCount
     */
    public function setFollowerCount(int $followerCount): void
    {
        if($followerCount >= 0){
            $this->followerCount = $followerCount;
        }else{
            $this->followerCount = 0;
        }
    }

    /**
     * @return string|null
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * @param string|null $location
     */
    public function setLocation(?string $location): void
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
}