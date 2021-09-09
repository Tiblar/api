<?php
namespace App\Entity\Media;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Entity\Media\MediaRepository")
 * @ORM\Table(indexes={
 *      @ORM\Index(name="post_idx", columns={"post_id"}),
 * })
 */
class Magnet
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
     * @ORM\Column(type="text")
     */
    private $magnet;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

    public function __construct()
    {
        $this->timestamp = new \DateTime();
    }

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
    public function getMagnet(): string
    {
        return $this->magnet;
    }

    /**
     * @param string $magnet
     */
    public function setMagnet(string $magnet): void
    {
        $this->magnet = $magnet;
    }

    /**
     * @return string
     */
    public function getTimestamp(): string
    {
        return $this->timestamp->format('c');
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
            'magnet' => $this->getMagnet(),
        ];
    }
}
