<?php
declare(strict_types=1);

namespace App\Entity\Media;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(
 *     repositoryClass="App\Entity\Media\MediaRepository"
 * )
 * @ORM\Table(
 *  indexes={
 *     @ORM\Index(name="id_idx", columns={"id"}),
 *     @ORM\Index(name="user_id_idx", columns={"user_id"}),
 *     @ORM\Index(name="file_id_idx", columns={"file_init_id", "part"}),
 *  },
 *  uniqueConstraints={
 *    @ORM\UniqueConstraint(
 *        name="file_part_unique",
 *        columns={"file_init_id", "part"}
 *    )
 *  }
 * )
 */
class FilePart
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
    private $userId;

    /**
     * @ORM\Column(type="string")
     */
    private $fileInitId;

    /**
     * @ORM\Column(type="integer")
     */
    private $part;

    /**
     * @ORM\Column(type="decimal", precision=16, scale=8)
     */
    private $partSize;

    /**
     * @ORM\Column(type="string")
     */
    private $ETag;

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
    public function getFileInitId(): string
    {
        return $this->fileInitId;
    }

    /**
     * @param string $fileInitId
     */
    public function setFileInitId(string $fileInitId): void
    {
        $this->fileInitId = $fileInitId;
    }

    /**
     * @return int
     */
    public function getPart(): int
    {
        return $this->part;
    }

    /**
     * @param int $part
     */
    public function setPart(int $part): void
    {
        $this->part = $part;
    }

    /**
     * @return float
     */
    public function getPartSize(): float
    {
        return floatval($this->partSize);
    }

    /**
     * @param float $partSize
     */
    public function setPartSize(float $partSize): void
    {
        $this->partSize = $partSize;
    }

    /**
     * @return string
     */
    public function getETag(): string
    {
        return $this->ETag;
    }

    /**
     * @param string $ETag
     */
    public function setETag(string $ETag): void
    {
        $this->ETag = $ETag;
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