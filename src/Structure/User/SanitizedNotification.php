<?php
declare(strict_types=1);

namespace App\Structure\User;

/**
 * User without sensitive information.
 */
class SanitizedNotification
{
    private $id = null;

    private $type = null;

    private $causers = [];

    private $interactionsCount = 0;

    private $message = null;

    private $post = null;

    private $seen = null;

    private $timestamp = null;

    public function __construct(array $arr)
    {
        if(isset($arr['id']) && is_string($arr['id'])){
            $this->setId($arr['id']);
        }

        if(isset($arr['type']) && is_string($arr['type'])){
            $this->setType($arr['type']);
        }

        if(isset($arr['interactionsCount']) && is_int($arr['interactionsCount'])){
            $this->setInteractionsCount($arr['interactionsCount']);
        }

        if(isset($arr['message']) && is_string($arr['message'])){
            $this->setMessage($arr['message']);
        }

        if(isset($arr['seen']) && is_bool($arr['seen'])){
            $this->setSeen($arr['seen']);
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
     * @return array|null
     */
    public function getPost(): ?array
    {
        return $this->post;
    }

    /**
     * @param array|null $post
     */
    public function setPost(?array $post): void
    {
        $this->post = $post;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @throws \Exception
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function getCausers(): array
    {
        return $this->causers;
    }

    /**
     * @param SanitizedUser $causer
     */
    public function addCauser(SanitizedUser $causer): void
    {
        $this->causers[] = $causer;
    }

    /**
     * @return mixed
     */
    public function getInteractionsCount(): int
    {
        return $this->interactionsCount;
    }

    /**
     * @param int $interactionsCount
     */
    public function setInteractionsCount(int $interactionsCount): void
    {
        if($interactionsCount < 0){
            $this->interactionsCount = 0;
        }else{
            $this->interactionsCount = $interactionsCount;
        }
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    /**
     * @param bool $seen
     * @throws \Exception
     */
    public function setSeen(bool $seen): void
    {
        $this->seen = $seen;
    }

    /**
     * @return bool
     */
    public function getSeen(): bool
    {
        return $this->seen;
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
        $causers = [];
        foreach($this->getCausers() as $causer){
            $causers[] = $causer->toArray();
        }

        $post = null;
        if(is_array($this->getPost())){
            $post = $this->getPost();
        }

        return [
            'id' => $this->getId(),
            'post' => $post,
            'type' => $this->getType(),
            'causers' => $causers,
            'interactions_count' => $this->getInteractionsCount(),
            'seen' => $this->getSeen(),
            'updated_timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}