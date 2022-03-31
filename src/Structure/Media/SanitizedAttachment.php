<?php
namespace App\Structure\Media;

class SanitizedAttachment
{
    private $id;

    private $postId;

    private $timestamp;

    private $row;

    private $originalName;

    private $thumbnails = [];

    private $file;

    private $available_transcoding;

    public function __construct(array $arr)
    {
        if(isset($arr['id'])){
            $this->setId($arr['id']);
        }

        if(isset($arr['postId'])){
            $this->setPostId($arr['postId']);
        }

        if(isset($arr['timestamp'])){
            $this->setTimestamp($arr['timestamp']);
        }

        if(isset($arr['row'])){
            $this->setRow($arr['row']);
        }

        if(isset($arr['originalName'])){
            $this->setOriginalName($arr['originalName']);
        }

        if(isset($arr['file']) && is_array($arr['file'])){
            $sanitizedFile = new SanitizedFile($arr['file']);
            $this->setFile($sanitizedFile);
        }

        if(isset($arr['thumbnails']) && is_array($arr['thumbnails'])){
            $this->setThumbnails($arr['thumbnails']);
        }

        if(isset($arr['available_transcoding'])){
            $this->setAvailableTranscoding($arr['available_transcoding']);
        }
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
    public function setId($id): void
    {
        $this->id = $id;
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
    public function setPostId($postId): void
    {
        $this->postId = $postId;
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
     * @return array
     */
    public function getAvailableTranscoding(): array
    {
        return $this->available_transcoding;
    }
    /**
     * @param array $row
     */
    public function setAvailableTranscoding(array $ats): void
    {
        $this->available_transcoding = $ats;
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
     * @return array
     */
    public function getThumbnails(): array
    {
        return $this->thumbnails;
    }

    /**
     * @param array $thumbnails
     */
    public function setThumbnails(array $thumbnails): void
    {
        $this->thumbnails = $thumbnails;
    }

    /**
     * @return SanitizedFile
     */
    public function getFile(): SanitizedFile
    {
        return $this->file;
    }

    /**
     * @param SanitizedFile $file
     */
    public function setFile(SanitizedFile $file): void
    {
        $this->file = $file;
    }

    /**
     * @return array
     * @throws \Exception
     */
    function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'post_id' => $this->getPostId(),
            'row' => $this->getRow(),
            'original_name' => $this->getOriginalName(),
            'thumbnails' => $this->getThumbnails(),
            'file' => $this->getFile()->toArray(),
            'timestamp' => $this->getTimestamp()->format('c'),
            'available_transcoding' => $this->getAvailableTranscoding(),
        ];
    }
}