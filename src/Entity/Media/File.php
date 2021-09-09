<?php
declare(strict_types=1);

namespace App\Entity\Media;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(
 *     repositoryClass="App\Entity\Media\MediaRepository"
 * )
 * @ORM\Table(indexes={
 *     @ORM\Index(name="id_idx", columns={"id"}),
 *     @ORM\Index(name="hash_idx", columns={"hash"}),
 * })
 */
class File
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private $id;

    /**
     * Deprecated
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="decimal", precision=16, scale=8)
     */
    private $fileSize;

    /**
     * Unique ID for file
     * SHA-1, MD5, or SHA-2
     *
     * @ORM\Column(type="string")
     */
    private $hash;

    /**
     * @ORM\Column(type="string")
     */
    private $hashName;

    /**
     * @ORM\Column(type="string")
     */
    private $extension;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $height;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $width;

    /**
     * If file is a video, how long it is in seconds
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $duration;

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
    public function getURL(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setURL(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return float
     */
    public function getFileSize(): float
    {
        return floatval($this->fileSize);
    }

    /**
     * @param float $fileSize
     */
    public function setFileSize(float $fileSize): void
    {
        $this->fileSize = $fileSize;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    /**
     * @return string
     */
    public function getHashName(): string
    {
        return $this->hashName;
    }

    /**
     * @param string $hashName
     */
    public function setHashName(string $hashName): void
    {
        $this->hashName = $hashName;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     */
    public function setExtension(string $extension): void
    {
        $this->extension = $extension;
    }

    /**
     * @return int
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @param int $height
     */
    public function setHeight(?int $height): void
    {
        $this->height = $height;
    }

    /**
     * @return int
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @param int|null $width
     */
    public function setWidth(?int $width): void
    {
        $this->width = $width;
    }

    /**
     * @return int
     */
    public function getDuration(): ?int
    {
        return $this->duration;
    }

    /**
     * @param int|null $duration
     */
    public function setDuration(?int $duration): void
    {
        $this->duration = $duration;
    }

    /**
     * @return array
     */
    function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'url' => $this->getURL(),
            'file_size' => $this->getFileSize(),
            'hash' => $this->getHash(),
            'hash_name' => $this->getHashName(),
            'height' => $this->getHeight(),
            'width' => $this->getWidth(),
            'duration' => $this->getDuration(),
        ];
    }
}