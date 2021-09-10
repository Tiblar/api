<?php
namespace App\Service\Content;

use App\Service\S3\Client;
use App\Service\Content\ContentException\InvalidException;
use App\Service\Content\Resources\Avatar;
use App\Service\Content\Resources\Banner;
use App\Service\Content\Resources\DefaultFile;
use App\Service\Content\Resources\Image;
use App\Service\Content\Resources\ResourceInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Resource
{
    const POST_TEXT = "POST_TEXT";
    const POST_IMAGE = "POST_IMAGE";
    const POST_VIDEO = "POST_VIDEO";
    const POST_AUDIO = "POST_AUDIO";
    const POST_PDF = "POST_PDF";
    const POST_MAGNET = "POST_MAGNET";
    const POST_FILE = "POST_FILE";
    const POST_POLL = "POST_POLL";
    const POST_STREAM = "POST_STREAM";

    /**
     * @var Client $blaze
     */
    private $blaze;

    /**
     * @var array $fileTypes
     */
    private $fileTypes;

    public function __construct(Client $blaze, array $fileTypes)
    {
        $this->blaze = $blaze;
        $this->fileTypes = $fileTypes;
    }

    public function getFile(UploadedFile $uploadedFile): ResourceInterface
    {
        $ext = $uploadedFile->guessClientExtension();
        $ext = str_replace('mpga', 'mp3', $ext);
        $ext = str_replace('qt', 'mov', $ext);

        $file = null;
        switch($this->getPostType($ext)){
            case self::POST_IMAGE:
                $file = new Image($uploadedFile, $this->blaze);
                break;
            case self::POST_AUDIO:
            case self::POST_VIDEO:
            case self::POST_FILE:
            case self::POST_PDF:
                $file = new DefaultFile($uploadedFile, $this->blaze, $this->getPostType($ext));
                break;
            default:
                throw new InvalidException();

        }

        return $file;
    }

    public function getAvatar(UploadedFile $uploadedFile)
    {
        return new Avatar($uploadedFile, $this->blaze);
    }

    public function getBanner(UploadedFile $uploadedFile)
    {
        return new Banner($uploadedFile, $this->blaze);
    }

    private function getPostType($ext)
    {
        if(in_array($ext, $this->fileTypes['image'])){
            return self::POST_IMAGE;
        }

        if(in_array($ext, $this->fileTypes['video'])){
            return self::POST_VIDEO;
        }

        if(in_array($ext, $this->fileTypes['audio'])){
            return self::POST_AUDIO;
        }

        if(in_array($ext, $this->fileTypes['pdf'])){
            return self::POST_PDF;
        }

        if(in_array($ext, $this->fileTypes['file'])){
            return self::POST_FILE;
        }

        throw new InvalidException();
    }
}
