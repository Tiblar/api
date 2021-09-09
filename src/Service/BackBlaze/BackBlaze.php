<?php
declare(strict_types=1);

namespace App\Service\BackBlaze;

use App\Service\Content\Resources\ResourceInterface;

class BackBlaze
{
    private $client;

    private $domain;

    private $bucket;

    private $dev;

    public function __construct(array $settings)
    {
        $this->bucket = $settings['bucket'];

        $this->domain = $settings['domain'];

        $this->dev = $settings['dev'];

        $this->client = new Client($settings['accountId'], [
            'keyId' => $settings['keyId'],
            'domainAliases' => [
                'f000.backblazeb2.com' => $settings['domain'],
            ],
            'applicationKey' => $settings['key'],
            'version' => 2,
            'largeFileLimit' => 100000000,
        ]);
    }

    /**
     * @param ResourceInterface $file
     * @return string URL of uploaded content
     */
    public function upload(ResourceInterface $file): string
    {
        $this->client->upload([
            'BucketName' => $this->bucket,
            'FileName' => $file->getHashName(),
            'Body' => $file->getContents()
        ]);

        if($this->dev){
            return '//' . $this->domain . '/' . $this->bucket . '/' . $file->getHash() . '.' . $file->getExtension();
        }

        return '//' . $this->domain . '/sneed-fs/' . $file->getHash() . '.' . $file->getExtension();
    }

    public function remove(string $filename): bool
    {
        return $this->client->deleteFile([
            'BucketName' => $this->bucket,
            'FileName' => $filename,
        ]);
    }

    public function startLargeFile(string $filename, string $sha1): string
    {
        $response = $this->client->startLargeFile([
            'BucketName' => $this->bucket,
            'FileName' => $filename,
            'Sha1' => $sha1,
        ]);

        return $response['fileId'];
    }

    public function uploadPart($data, string $fileId, int $partNumber)
    {
        $this->client->uploadPart($data, $fileId, $partNumber);
    }

    public function finishLargeFile(string $fileId, array $sha1Parts, string $backBlazeFileName)
    {
        $this->client->finishLargeFile($fileId, $sha1Parts);

        if($this->dev){
            return '//' . $this->domain . '/' . $this->bucket . '/' . $backBlazeFileName;
        }

        return '//' . $this->domain . '/sneed-fs/' . $backBlazeFileName;
    }

    public function deleteLargeFile(string $fileId)
    {
        $this->client->deleteLargeFile($fileId);
    }
}