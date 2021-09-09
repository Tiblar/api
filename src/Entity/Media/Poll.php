<?php
namespace App\Entity\Media;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Entity\Media\MediaRepository")
 * @ORM\Table(indexes={
 *      @ORM\Index(name="post_idx", columns={"post_id"}),
 * })
 */
class Poll
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $postId;

    /**
     * @ORM\Column(type="string")
     */
    private $question;

    /**
     * @ORM\Column(type="string")
     */
    private $o1;

    /**
     * @ORM\Column(type="string")
     */
    private $o2;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $o3;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $o4;

    /**
     * @ORM\Column(type="integer")
     */
    private $o1VotesCount = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $o2VotesCount = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $o3VotesCount = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $o4VotesCount = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $votesCount = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expireTimestamp;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
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
     * @return string
     */
    public function getOptionOne(): string
    {
        return $this->o1;
    }

    /**
     * @param string $o1
     */
    public function setOptionOne(string $o1): void
    {
        $this->o1 = $o1;
    }

    /**
     * @return string
     */
    public function getOptionTwo(): string
    {
        return $this->o2;
    }

    /**
     * @param string $o2
     */
    public function setOptionTwo(string $o2): void
    {
        $this->o2 = $o2;
    }

    /**
     * @return string
     */
    public function getOptionThree(): ?string
    {
        return $this->o3;
    }

    /**
     * @param string $o3
     */
    public function setOptionThree(string $o3): void
    {
        $this->o3 = $o3;
    }

    /**
     * @return string
     */
    public function getOptionFour(): ?string
    {
        return $this->o4;
    }

    /**
     * @param string $o4
     */
    public function setOptionFour(string $o4): void
    {
        $this->o4 = $o4;
    }

    /**
     * @return int
     */
    public function getO1VotesCount(): int
    {
        return $this->o1VotesCount;
    }

    /**
     * @param int $o1VotesCount
     */
    public function setO1VotesCount(int $o1VotesCount): void
    {
        $this->o1VotesCount = $o1VotesCount;
    }

    /**
     * @return int
     */
    public function getO2VotesCount(): int
    {
        return $this->o2VotesCount;
    }

    /**
     * @param int $o2VotesCount
     */
    public function setO2VotesCount(int $o2VotesCount): void
    {
        $this->o2VotesCount = $o2VotesCount;
    }

    /**
     * @return int
     */
    public function getO3VotesCount(): int
    {
        return $this->o3VotesCount;
    }

    /**
     * @param int $o3VotesCount
     */
    public function setO3VotesCount(int $o3VotesCount): void
    {
        $this->o3VotesCount = $o3VotesCount;
    }

    /**
     * @return int
     */
    public function getO4VotesCount(): int
    {
        return $this->o4VotesCount;
    }

    /**
     * @param int $o4VotesCount
     */
    public function setO4VotesCount(int $o4VotesCount): void
    {
        $this->o4VotesCount = $o4VotesCount;
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
     * @return bool
     */
    public function isExpired(): bool
    {
        return ($this->getExpireTimestamp() < new \DateTime());
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
    public function toArray(): array
    {
        $options = [];

        if(!is_null($this->getOptionOne())){
            $options[] = $this->getOptionOne();
        }

        if(!is_null($this->getOptionTwo())){
            $options[] = $this->getOptionTwo();
        }

        if(!is_null($this->getOptionThree())){
            $options[] = $this->getOptionThree();
        }

        if(!is_null($this->getOptionFour())){
            $options[] = $this->getOptionFour();
        }

        return [
            'id' => $this->getId(),
            'question' => $this->getQuestion(),
            'options' => $options,
            'expire_timestamp' => $this->getExpireTimestamp()->format('c'),
        ];
    }
}
