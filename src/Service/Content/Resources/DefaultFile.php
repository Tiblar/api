<?php
declare(strict_types=1);

namespace App\Service\Content\Resources;

use App\Service\BackBlaze\BackBlaze;
use App\Service\Content\Resource;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DefaultFile implements ResourceInterface
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
    private $postType;

    /**
     * @var string
     */
    private $originalName;

    /**
     * @var string
     */
    private $extension;

    /**
     * @var BackBlaze
     */
    private $blaze;

    /**
     * @var string
     */
    private $url;

    public function __construct(UploadedFile $uploadedFile, BackBlaze $blaze, string $postType)
    {
        $this->blaze = $blaze;

        $this->contents = file_get_contents($uploadedFile->getRealPath());

        $ext = $uploadedFile->guessClientExtension();
        $ext = str_replace('mpga', 'mp3', $ext);
        $ext = str_replace('qt', 'mov', $ext);

        $this->extension = $ext;
        $this->hash = hash_file("sha256", $uploadedFile->getRealPath());
        $this->hashname = $this->hash . "." . $ext;

        $fileSize = $uploadedFile->getSize() / 1000 / 1000 / 1000;
        $this->fileSize = number_format($fileSize, 8);

        $this->postType = $postType;

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
