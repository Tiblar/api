<?php
namespace App\Service\Content\Resources;

use App\Service\S3\Client;
use App\Service\Content\Resource;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Image implements ResourceInterface
{
    /**
     * @var false|resource
     */
    private $image;

    /**
     * @var string
     */
    private $contents;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var string
     */
    private $hash;

    /**
     * @var string
     */
    private $hashname;

    /**
     * @var string
     */
    private $fileSize;

    /**
     * @var string
     */
    private $postType;

    /**
     * @var string
     */
    private $originalName;

    /**
     * @var
     */
    private $extension;

    /**
     * @var Client
     */
    private $blaze;

    /**
     * @var string
     */
    private $url;

    public function __construct(UploadedFile $uploadedFile, Client $blaze)
    {
        $this->blaze = $blaze;

        $this->contents = file_get_contents($uploadedFile->getRealPath());

        $ext = $uploadedFile->guessClientExtension();

        $this->extension = $ext;
        $this->hash = hash_file("sha256", $uploadedFile->getRealPath());
        $this->hashname = $this->hash . "." . $ext;

        $info = getimagesize($uploadedFile);
        list($x, $y) = $info;

        $this->width = $x;
        $this->height = $y;

        $fileSize = $uploadedFile->getSize() / 1024 / 1024 / 1024;
        $this->fileSize = number_format($fileSize, 8);

        $this->postType = Resource::POST_IMAGE;

        $this->originalName = $uploadedFile->getClientOriginalName();
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        return $this->contents;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @return string
     */
    public function getHashName(): string
    {
        return $this->hashname;
    }

    /**
     * @return string
     */
    public function getFileSize(): string
    {
        return $this->fileSize;
    }

    /**
     * @return string
     */
    public function getPostType(): string
    {
        return $this->postType;
    }

    /**
     * @return string
     */
    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function resize(int $width, int $height): void
    {
        $thumbnail = new \Imagick();
        $thumbnail->readImageBlob($this->contents);
        $thumbnail->resizeImage($width, $height,\Imagick::FILTER_LANCZOS,1);
        $this->contents = $thumbnail->getImageBlob();
        $this->hash = hash("sha256", $this->contents);
        $this->hashname = $this->hash . "." . $this->getExtension();

        $fileSize = $thumbnail->getImageLength() / 1024 / 1024 / 1024;
        $this->fileSize = number_format($fileSize, 8);

        $this->width = $width;
        $this->height = $height;
        $this->url = null;

        $thumbnail->clear();
        $thumbnail->destroy();
    }

    public function maxSize(float $size, $runs = 0)
    {
        $compress = new \Imagick();
        $compress->readImageBlob($this->contents);
        $compress->setImageCompression(\Imagick::COMPRESSION_JPEG);
        $compress->setImageCompressionQuality(75);
        $this->contents = $compress->getImageBlob();
        $this->hash = hash("sha256", $this->contents);
        $this->hashname = $this->hash . "." . $this->getExtension();

        $fileSize = $compress->getImageLength() / 1024 / 1024 / 1024;
        $this->fileSize = number_format($fileSize, 8);

        $this->url = null;

        $compress->clear();
        $compress->destroy();

        if($size > $this->getFileSize() && $runs < 3){
            $this->maxSize($size, $runs + 1);
        }
    }

    public function convertToJpg(): void
    {
        $image = new \Imagick();
        $image->readImageBlob($this->contents);
        $image->setImageBackgroundColor('black');
        $image->setImageFormat('jpg');

        $this->contents = $image->getImageBlob();
        $this->hash = hash("sha256", $this->contents);
        $this->hashname = $this->hash . ".jpg";
        $this->extension = 'jpg';

        $fileSize = $image->getImageLength() / 1024 / 1024 / 1024;
        $this->fileSize = number_format($fileSize, 8);

        $image->clear();
        $image->destroy();
    }

    public function upload(): string
    {
        if(!is_null($this->url)) return $this->url;

        $this->url = $this->blaze->upload($this);

        return $this->url;
    }

    public function delete(): bool
    {
        if(is_null($this->url)) return false;

        return $this->blaze->remove($this->getHashName());
    }
}
