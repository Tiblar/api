<?php
declare(strict_types=1);

namespace App\Entity\Media;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(
 *     repositoryClass="App\Entity\Media\MediaRepository"
 * )
 * @ORM\Table(indexes={
 *     @ORM\Index(name="id_idx", columns={"id"}),
 *     @ORM\Index(name="s3_file_id_idx", columns={"s3_file_id"}),
 *     @ORM\Index(name="user_id_idx", columns={"user_id", "status"}),
 * })
 */
class FileInit
{
    static $FILE_INIT_PROCESSING = "PROCESSING";
    static $FILE_INIT_FINISHED = "FINISHED";

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
    private $status;

    /**
     * @ORM\Column(type="string")
     */
    private $userId;

    /**
     * @ORM\Column(type="integer")
     */
    private $partCount;

    /**
     * @ORM\Column(type="string")
     */
    private $s3FileId;

    /**
     * @ORM\Column(type="decimal", precision=16, scale=8)
     */
    private $fileSize;

    /**
     * @ORM\Column(type="decimal", precision=16, scale=8)
     */
    private $maxFileSize;

    /**
     * @ORM\Column(type="string")
     */
    private $hash;

    /**
     * @ORM\Column(type="string")
     */
    private $extension;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $originalName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $fileId;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

    public function __construct()
    {
        $this->timestamp = new \DateTime();
        $this->status = self::$FILE_INIT_PROCESSING;
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
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        if(!in_array($status, [self::$FILE_INIT_PROCESSING, self::$FILE_INIT_FINISHED])){

        }

        $this->status = $status;
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
    public function getPartCount(): int
    {
        return $this->partCount;
    }

    /**
     * @param int $partCount
     */
    public function setPartCount(int $partCount): void
    {
        $this->partCount = $partCount;
    }

    /**
     * @return string
     */
    public function getS3FileId(): string
    {
        return $this->s3FileId;
    }

    /**
     * @param string $fileId
     */
    public function setS3FileId(string $fileId): void
    {
        $this->s3FileId = $fileId;
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
     * @return float
     */
    public function getMaxFileSize(): float
    {
        return floatval($this->maxFileSize);
    }

    /**
     * @param float $maxFileSize
     */
    public function setMaxFileSize(float $maxFileSize): void
    {
        $this->maxFileSize = $maxFileSize;
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
     * @return string
     */
    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    /**
     * @param string $originalName
     */
    public function setOriginalName(string $originalName): void
    {
        $this->originalName = $originalName;
    }

    /**
     * @return string
     */
    public function getFileId(): ?string
    {
        return $this->fileId;
    }

    /**
     * @param string $fileId
     */
    public function setFileId(string $fileId): void
    {
        $this->fileId = $fileId;
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