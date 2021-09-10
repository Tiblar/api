<?php
namespace App\Structure\Media;

class SanitizedFile
{
    private $id;

    private $fileSize;

    private $hash;

    private $hashName;

    private $height;

    private $width;

    private $duration;

    public function __construct(array $arr)
    {
        if(isset($arr['id'])){
            $this->setId($arr['id']);
        }

        if(isset($arr['fileSize'])){
            $this->setFileSize($arr['fileSize']);
        }

        if(isset($arr['file_size'])){
            $this->setFileSize($arr['file_size']);
        }

        if(isset($arr['hash'])){
            $this->setHash($arr['hash']);
        }

        if(isset($arr['hashName'])){
            $this->setHashName($arr['hashName']);
        }

        if(isset($arr['hash_name'])){
            $this->setHashName($arr['hash_name']);
        }

        if(isset($arr['height'])){
            $this->setHeight($arr['height']);
        }

        if(isset($arr['width'])){
            $this->setWidth($arr['width']);
        }

        if(isset($arr['duration'])){
            $this->setDuration($arr['duration']);
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
    public function getURL(): string
    {
        return '//' . $_ENV['S3_DOMAIN'] . '/' . $_ENV['S3_BUCKET'] . '/' . $this->getHashName();
    }

    /**
     * @return string
     */
    public function getFileSize(): string
    {
        return $this->fileSize;
    }

    /**
     * @param string $fileSize
     */
    public function setFileSize(string $fileSize): void
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
            'height' => $this->getHeight(),
            'width' => $this->getWidth(),
            'duration' => $this->getDuration(),
        ];
    }
}