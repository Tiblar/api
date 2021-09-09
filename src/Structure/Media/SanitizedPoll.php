<?php
namespace App\Structure\Media;

class SanitizedPoll
{
    private $id;

    private $postId;

    private $votesCount;

    private $question;

    private $options;

    private $myVote;

    private $expireTimestamp;

    public function __construct(array $arr)
    {
        if(isset($arr['id'])){
            $this->setId($arr['id']);
        }

        if(isset($arr['postId'])){
            $this->setPostId($arr['postId']);
        }

        if(isset($arr['question'])){
            $this->setQuestion($arr['question']);
        }

        if(isset($arr['votesCount'])){
            $this->setVotesCount($arr['votesCount']);
        }

        if(isset($arr['expireTimestamp'])){
            $this->setExpireTimestamp($arr['expireTimestamp']);
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
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getPostId(): string
    {
        return $this->postId;
    }

    /**
     * @param string $postId
     */
    public function setPostId(string $postId): void
    {
        $this->postId = $postId;
    }

    /**
     * @return string
     */
    public function getQuestion(): string
    {
        return $this->question;
    }

    /**
     * @param string $question
     */
    public function setQuestion(string $question): void
    {
        $this->question = $question;
    }

    /**
     * @return int
     */
    public function getVotesCount(): int
    {
        return $this->votesCount;
    }

    /**
     * @param int $votesCount
     */
    public function setVotesCount(int $votesCount): void
    {
        $this->votesCount = $votesCount;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        $options = [];

        foreach($this->options as $option){
            if(isset($option['title']) && isset($option['votes_count'])){
                $options[] = $option;
            }
        }

        return $options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * @param array $option
     */
    public function addOption(array $option) {
        $this->options[] = $option;
    }

    /**
     * @return array|null
     */
    public function getMyVote(): ?array
    {
        $myVote = $this->myVote;

        if(!isset($myVote['id']) || !isset($myVote['option'])){
            return null;
        }

        return $myVote;
    }

    /**
     * @param array $myVote
     */
    public function setMyVote(array $myVote): void
    {
        $this->myVote = $myVote;
    }

    /**
     * @return \DateTime
     */
    public function getExpireTimestamp(): \DateTime
    {
        return $this->expireTimestamp;
    }

    /**
     * @param \DateTime $expireTimestamp
     */
    public function setExpireTimestamp(\DateTime $expireTimestamp): void
    {
        $this->expireTimestamp = $expireTimestamp;
    }

    /**
     * @return array
     */
    function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'votes_count' => $this->getVotesCount(),
            'question' => $this->getQuestion(),
            'options' => $this->getOptions(),
            'my_vote' => $this->getMyVote(),
            'expired' => ($this->getExpireTimestamp() < new \DateTime()),
            'expire_timestamp' => $this->getExpireTimestamp()->format('c'),
        ];
    }
}