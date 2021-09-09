<?php
declare(strict_types=1);

namespace App\Entity\Media;

use App\Entity\Post\Post;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Entity\Media\MediaRepository")
 * @ORM\Table(indexes={@ORM\Index(name="attachment_idx", columns={"id", "attachment_post_id"})})
 */
class Attachment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Post\Post", inversedBy="attachments")
     * @ORM\JoinColumn(name="attachment_post_id", referencedColumnName="id")
     */
    private $post;

    /**
     * @ORM\Column(name="`row`", type="integer", options={"default":"0"})
     */
    private $row = 0;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $originalName;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Media\File", fetch="EAGER")
     * @ORM\JoinColumn(name="attachment_file_id", referencedColumnName="id")
     */
    private $file;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Media\Thumbnail", mappedBy="attachment", fetch="EAGER", cascade={"remove", "persist"}, orphanRemoval=true
     * )
     * @ORM\JoinColumn(name="attachment_thumbnail_id", referencedColumnName="id")
     */
    private $thumbnails;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

    public function __construct()
    {
        $this->timestamp = new \DateTime();
        $this->row = 0;
        $this->thumbnails = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return Post
     */
    public function getPost(): Post
    {
        return $this->post;
    }

    /**
     * @param Post $post
     */
    public function setPost(Post $post): void
    {
        $this->post = $post;
    }

    /**
     * @return int
     */
    public function getRow(): int
    {
        return $this->row;
    }

    /**
     * @param int $row
     */
    public function setRow(int $row): void
    {
        $this->row = $row;
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
     * @return File|null
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @param File|null $file
     */
    public function setFile(?File $file): void
    {
        $this->file = $file;
    }


    /**
     * @return Collection
     */
    public function getThumbnails(): Collection
    {
        return $this->thumbnails;
    }

    private function getThumbnailsArray(): array
    {
        $thumbnails = [];
        foreach($this->getThumbnails()->toArray() as $thumbnail){
            $thumbnails[] = $thumbnail->toArray();
        }

        return $thumbnails;
    }

    /**
     * @param Thumbnail $thumbnail
     */
    public function addThumbnail(Thumbnail $thumbnail): void
    {
        $this->thumbnails->add($thumbnail);
    }

    /**
     * @param Thumbnail[] $thumbnails
     */
    public function setThumbnails(array $thumbnails): void
    {
        foreach($thumbnails as $thumbnail){
            $this->addThumbnail($thumbnail);
        }
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
     * @return bool
     */
    public function isValid(): bool
    {
        return !is_null($this->getFile());
    }

    /**
     * @return array
     */
    function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'row' => $this->getRow(),
            'original_name' => $this->getOriginalName(),
            'thumbnails' => $this->getThumbnailsArray(),
            'file' => $this->getFile()->toArray(),
            'timestamp' => $this->getTimestamp(),
        ];
    }
}