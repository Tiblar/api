<?php
namespace App\Service\Content\Resources;

use App\Service\S3\Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Banner implements ResourceInterface
{
    /**
     * @var string
     */
    private $contents;

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
    private $originalName;

    /**
     * @var Client
     */
    private $blaze;

    /**
     * @var string
     */
    private $url;

    public function __construct(UploadedFile $file, Client $blaze)
    {
        $this->blaze = $blaze;

        $avatar = new \Imagick();
        $avatar->readImage($file->getRealPath());
        $avatar->resizeImage(700,200,\Imagick::FILTER_LANCZOS,1);

        $this->contents = $avatar->getImageBlob();

        $this->hash = hash("sha256", $this->contents);
        $this->hashname = $this->hash . ".png";

        $fileSize = $avatar->getImageLength() / 1024 / 1024 / 1024;
        $this->fileSize = number_format($fileSize, 8);

        $this->originalName = pathinfo($file->getFilename(), PATHINFO_BASENAME);

        $avatar->clear();
        $avatar->destroy();
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
        return 610;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return 200;
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

    public function getExtension(): string
    {
        return 'png';
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
    public function getPostType(): ?string
    {
        return null;
    }

    /**
     * @return string
     */
    public function getOriginalName(): string
    {
        return $this->originalName;
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
