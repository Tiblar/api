<?php
namespace App\Entity\Analytics;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Entity\Analytics\AnalyticsRepository")
 * @ORM\Table(
 *     indexes={
 *      @ORM\Index(name="analytics_idx", columns={"user_id", "post_id", "timestamp"}),
 *      @ORM\Index(name="analytics_post_idx", columns={"post_id"})
 *     },
 *     name="analytics_post"
 * )
 */
class PostAnalytics
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private string $id;

    /**
     * Person who is viewed
     *
     * @ORM\Column(type="string")
     */
    private string $userId;

    /**
     * Post that is viewed
     *
     * @ORM\Column(type="string")
     */
    private string $postId;

    /**
     * Number of views
     *
     * @ORM\Column(type="bigint")
     */
    private int $views;

    /**
     * Source of view (dashboard, profile, etc)
     *
     * @ORM\Column(type="string")
     */
    private string $source;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $timestamp;

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
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     */
    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
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
     * @return int
     */
    public function getViews(): int
    {
        return $this->views;
    }

    /**
     * @param int $views
     */
    public function setViews(int $views): void
    {
        $this->views = $views;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     * @throws \Exception
     */
    public function setSource(string $source): void
    {
        if(in_array($source, ViewLog::getSourceTypes())){
            $this->source = $source;
        }else{
            throw new \Exception("Invalid view log source");
        }
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

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'user_id' => $this->getUserId(),
            'views' => $this->getViews(),
            'timestamp' => $this->getTimestamp()->format('c')
        ];
    }
}
