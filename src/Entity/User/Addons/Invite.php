<?php
namespace App\Entity\User\Addons;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="invite_inviter_idx", columns={"inviter", "complete"}),
 *     @ORM\Index(name="invite_invited_idx", columns={"invited"}),
 * })
 */
class Invite
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private $id;

    /**
     * ID of person who invites
     *
     * @ORM\Column(type="string")
     */
    private $inviter;

    /**
     * ID of person who invites
     *
     * @ORM\Column(type="string")
     */
    private $invited;

    /**
     * @ORM\Column(type="boolean")
     */
    private $complete;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp = null;

    public function __construct()
    {
        $this->timestamp = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getInviter(): string
    {
        return $this->inviter;
    }

    /**
     * @param string $inviter
     */
    public function setInviter(string $inviter): void
    {
        $this->inviter = $inviter;
    }

    /**
     * @return string
     */
    public function getInvited(): string
    {
        return $this->invited;
    }

    /**
     * @param $invited
     */
    public function setInvited(string $invited): void
    {
        $this->invited = $invited;
    }

    /**
     * @return bool
     */
    public function getComplete(): bool
    {
        return $this->complete;
    }

    /**
     * @param $complete
     */
    public function setComplete(bool $complete)
    {
        $this->complete = $complete;
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
}
