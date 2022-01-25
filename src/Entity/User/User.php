<?php
namespace App\Entity\User;

use App\Entity\User\Addons\ConfirmEmail;
use App\Entity\User\Addons\Privacy;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Entity\User\UserRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="user_idx", columns={"id"}),
 *     @ORM\Index(name="user_info_idx", columns={"id", "user_info_id"})
 * })
 */
class User implements UserInterface
{
    static $TWO_FACTOR_EMAIL = "TWO_FACTOR_EMAIL";

    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User\UserInfo", fetch="EAGER", cascade={"remove"})
     * @ORM\JoinColumn(name="user_info_id", referencedColumnName="id")
     */
    private $info;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User\Addons\Privacy", fetch="LAZY", cascade={"remove"})
     * @ORM\JoinColumn(name="user_privacy_id", referencedColumnName="id")
     */
    private $privacy;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User\Addons\ConfirmEmail", fetch="LAZY", cascade={"remove"})
     * @ORM\JoinColumn(name="user_confirm_email_id", referencedColumnName="id")
     */
    private $confirmEmail;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="boolean")
     */
    private $verified = false;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = ["ROLE_USER"];

    /**
     * @ORM\Column(type="boolean")
     */
    private $boosted = false;

    /**
     * GB limit
     *
     * @ORM\Column(type="decimal", precision=16, scale=8)
     */
    private $storageLimit = 0.5;

    /**
     * GB stored
     *
     * @ORM\Column(type="decimal", precision=16, scale=8)
     */
    private $storage = 0;

    /**
     * @ORM\Column(type="string")
     */
    private $theme = "light";

    /**
     * Filter adult content
     *
     * @ORM\Column(type="boolean")
     */
    private $nsfwFilter = true;

    /**
     * 1 enables 2fa, 0 disables
     *
     * @ORM\Column(type="boolean")
     */
    private $twoFactor = false;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $twoFactorType;

    /**
     * @ORM\Column(type="boolean")
     */
    private $banned = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $deleted = false;

    public function __construct($info)
    {
        $userInfo = new UserInfo($info);

        $this->info = $userInfo;
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
     * @return UserInfo
     */
    public function getInfo(): UserInfo
    {
        return $this->info;
    }

    /**
     * @param UserInfo|null $info
     */
    public function setInfo(?UserInfo $info)
    {
        $this->info = $info;
    }

    /**
     * @return Privacy
     */
    public function getPrivacy(): ?Privacy
    {
        return $this->privacy;
    }

    /**
     * @param Privacy|null $privacy
     */
    public function setPrivacy(?Privacy $privacy)
    {
        $this->privacy = $privacy;
    }

    /**
     * @param ConfirmEmail|null $confirm
     */
    public function setConfirmEmail(?ConfirmEmail $confirm)
    {
        $this->confirmEmail = $confirm;
    }

    /**
     * @return ConfirmEmail
     */
    public function getConfirmEmail(): ?ConfirmEmail
    {
        return $this->confirmEmail;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return bool
     */
    public function getVerified(): bool
    {
        return $this->verified;
    }

    /**
     * @param bool $verified
     */
    public function setVerified(bool $verified): void
    {
        $this->verified = $verified;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param string $role
     */
    public function addRole(string $role): void
    {
        $roles = $this->getRoles();
        $roles[] = $role;

        $this->roles = array_unique($roles);
    }

    /**
     * @return bool
     */
    public function isBoosted(): bool
    {
        return $this->boosted;
    }

    /**
     * @param bool $boosted
     */
    public function setBoosted(bool $boosted): void
    {
        $this->boosted = $boosted;
    }

    /**
     * @return float
     */
    public function getStorageLimit(): float
    {
        return $this->storageLimit;
    }

    /**
     * @param float $storageLimit
     */
    public function setStorageLimit(float $storageLimit): void
    {
        $this->storageLimit = $storageLimit;
    }

    /**
     * @return float
     */
    public function getStorage(): float
    {
        return $this->storage;
    }

    /**
     * @param float $storage
     */
    public function setStorage(float $storage): void
    {
        $this->storage = $storage;

        if($this->storage < 0){
            $this->storage = 0;
        }
    }

    /**
     * @return string
     */
    public function getTheme(): string
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     */
    public function setTheme(string $theme): void
    {
        $this->theme = $theme;
    }

    /**
     * @return bool
     */
    public function getNsfwFilter(): bool
    {
        return $this->nsfwFilter;
    }

    /**
     * @param bool $nsfwFilter
     */
    public function setNsfwFilter(bool $nsfwFilter): void
    {
        $this->nsfwFilter = $nsfwFilter;
    }

    /**
     * @return bool
     */
    public function isTwoFactor(): bool
    {
        return $this->twoFactor;
    }

    /**
     * @param bool $twoFactor
     */
    public function setTwoFactor(bool $twoFactor): void
    {
        $this->twoFactor = $twoFactor;
    }

    /**
     * @return string
     */
    public function getTwoFactorType(): ?string
    {
        return $this->twoFactorType;
    }

    /**
     * @param string|null $twoFactorType
     * @throws \Exception
     */
    public function setTwoFactorType(?string $twoFactorType): void
    {
        if(in_array($twoFactorType, [self::$TWO_FACTOR_EMAIL, null])){
            $this->twoFactorType = $twoFactorType;
        }else{
            throw new \Exception("Invalid two factor type");
        }
    }

    /**
     * @return bool
     */
    public function isBanned() : bool
    {
        return $this->banned;
    }

    /**
     * @param bool $banned
     */
    public function setBanned(bool $banned): void
    {
        $this->banned = $banned;
    }

    /**
     * @return bool
     */
    public function isDeleted() : bool
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     */
    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted;
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * Identifier
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->getId();
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
}