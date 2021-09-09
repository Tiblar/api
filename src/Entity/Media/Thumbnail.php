<?php
declare(strict_types=1);

namespace App\Entity\Media;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Entity\Media\MediaRepository")
 * @ORM\Table(indexes={@ORM\Index(name="thumbnail_idx", columns={"id"})})
 */
class Thumbnail
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Media\Attachment", inversedBy="thumbnails")
     * @ORM\JoinColumn(name="thumbanil_attachment_id", referencedColumnName="id")
     */
    private $attachment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Media\File", fetch="EAGER")
     * @ORM\JoinColumn(name="attachment_file_id", referencedColumnName="id")
     */
    private $file;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

    public function __construct()
    {
        $this->timestamp = new \DateTime();
        $this->row = 0;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return Attachment
     */
    public function getAttachment(): Attachment
    {
        return $this->attachment;
    }

    /**
     * @param Attachment $attachment
     */
    public function setAttachment(Attachment $attachment): void
    {
        $this->attachment = $attachment;
    }

    /**
     * @return File
     */
    public function getFile(): File
    {
        return $this->file;
    }

    /**
     * @param File $file
     */
    public function setFile(File $file): void
    {
        $this->file = $file;
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
    function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'file' => $this->getFile()->toArray(),
            'timestamp' => $this->getTimestamp(),
        ];
    }
}