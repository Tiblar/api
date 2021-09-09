<?php
declare(strict_types=1);

namespace App\Structure\User;

use App\Entity\User\Addons\ConfirmEmail;
use App\Entity\User\Addons\Privacy;
use App\Entity\User\User;

/**
 * User without sensitive information.
 */
class SanitizedUser
{

    /**
     * @var string
     */
    private $id;

    /**
     * @var SanitizedUserInfo
     */
    private $info;

    /**
     * @var SanitizedPrivacy
     */
    private $privacy;

    /**
     * @var bool
     */
    private $verified;

    /**
     * @var array
     */
    private $roles;

    /**
     * @var array
     */
    private $connections;

    /**
     * @var bool
     */
    private $boosted;

    /**
     * @var bool
     */
    private $banned;

    /**
     * SanitizedUser constructor.
     * @param User $user
     * @param bool $addons
     */
    public function __construct(User $user, bool $addons = false)
    {
        $this->id = $user->getId();
        if($user->getPrivacy() INSTANCEOF Privacy){
            $this->privacy = new SanitizedPrivacy($user->getPrivacy());
        }

        $privacy = $addons ? null : $this->getPrivacy();
        $this->info = new SanitizedUserInfo($user->getInfo(), $privacy);

        $this->roles = $user->getRoles();
        $this->connections = null;
        $this->verified = $user->getVerified();
        $this->boosted = $user->isBoosted();
        $this->banned = $user->isBanned();
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
     * @return SanitizedUserInfo
     */
    public function getInfo(): SanitizedUserInfo
    {
        return $this->info;
    }

    /**
     * @return SanitizedPrivacy
     */
    public function getPrivacy(): ?SanitizedPrivacy
    {
        return $this->privacy;
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
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @return null|array
     */
    public function getConnections(): ?array
    {
        return $this->connections;
    }

    /**
     * @param array $connection
     */
    public function addConnection(array $connection): void
    {
        $this->connections[] = $connection;
    }

    /**
     * @param array $connections
     */
    public function setConnections(array $connections): void
    {
        $this->connections = $connections;
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
     * @return array
     */
    public function toArray(): array
    {
        $privacy = null;
        $confirmEmail = null;

        if(!is_null($this->getPrivacy())){
            $privacy = $this->getPrivacy()->toArray();
        }

        $user = [
            'id' => $this->getId(),
            'info' => $this->getInfo()->toArray(),
            'privacy' => $privacy,
            'verified' => $this->getVerified(),
            'roles' => $this->getRoles(),
            'boosted' => $this->isBoosted(),
            'banned' => $this->isBanned(),
        ];

        if(!is_null($this->getConnections()) && is_array($this->getConnections())){
            $user['connections'] = $this->getConnections();
        }

        return $user;
    }
}