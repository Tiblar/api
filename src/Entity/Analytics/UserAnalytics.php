<?php
namespace App\Entity\Analytics;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Entity\Analytics\AnalyticsRepository")
 * @ORM\Table(
 *     indexes={
 *      @ORM\Index(name="analytics_idx", columns={"user_id", "timestamp"}),
 *      @ORM\Index(name="analytics_user_idx", columns={"user_id"})
 *     },
 *     name="analytics_user"
 * )
 */
class UserAnalytics
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
     * Number of views
     *
     * @ORM\Column(type="bigint")
     */
    private int $views;

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
