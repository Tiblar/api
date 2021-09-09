<?php
namespace App\Structure\User;

use App\Entity\User\User;

class SanitizedFollowRequest {

    private $id = null;

    private $requested = null;

    private $requester = null;

    private $timestamp = null;

    public function __construct(array $arr)
    {
        if(isset($arr['id']) && is_string($arr['id'])){
            $this->setId($arr['id']);
        }

        if(isset($arr['timestamp']) && $arr['timestamp'] INSTANCEOF \DateTime){
            $this->setTimestamp($arr['timestamp']);
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
    public function getRequested(): SanitizedUser
    {
        return new SanitizedUser($this->requested);
    }

    /**
     * @param User $user
     */
    public function setRequested(User $user): void
    {
        $this->requested = $user;
    }

    /**
     * @return SanitizedUser
     */
    public function getRequester(): SanitizedUser
    {
        return new SanitizedUser($this->requester);
    }

    /**
     * @param User $user
     */
    public function setRequester(User $user): void
    {
        $this->requester = $user;
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
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'requested' => $this->getRequested()->toArray(),
            'requester' => $this->getRequester()->toArray(),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}