<?php
namespace App\Entity\SpamFilter;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="spam_filter_ip_list", indexes={
 *      @ORM\Index(name="ip_idx", columns={"ip_address"}),
 * })
 */
class IpList
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private string $id;

    /**
     * @ORM\Column(type="string")
     */
    private string $ipAddress;

    /**
     * @ORM\Column(type="decimal")
     */
    private int $rating;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private \DateTime $updatedTimestamp;

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
    public function getIp(): string
    {
        return $this->ipAddress;
    }

    /**
     * @param string $ipAddress
     */
    public function setIp(string $ipAddress): void
    {
        $this->ipAddress = $ipAddress;
    }

    /**
     * @return float
     */
    public function getRating(): float
    {
        return $this->rating;
    }

    /**
     * @param float $rating
     */
    public function setRating(float $rating): void
    {
        $this->rating = $rating;

        if($this->rating > 1){
            $this->rating = 1;
        }
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedTimestamp(): ?\DateTime
    {
        return $this->updatedTimestamp;
    }

    /**
     * @param \DateTime $updatedTimestamp
     */
    public function setUpdatedTimestamp(\DateTime $updatedTimestamp): void
    {
        $this->updatedTimestamp = $updatedTimestamp;
    }
}