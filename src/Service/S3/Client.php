<?php
declare(strict_types=1);

namespace App\Service\S3;

use App\Service\Content\Resources\ResourceInterface;

class Client
{
    private \Aws\S3\S3Client $client;

    private string $bucket;

    public function __construct(array $settings)
    {
        $this->bucket = $settings['bucket'];

        $this->client = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region'  => $settings['region'],
            'endpoint' => $settings['endpoint'],
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key'    => $settings['key'],
                'secret' => $settings['secret'],
            ],
        ]);
    }

    /**
     * @param ResourceInterface $file
     * @return string URL of uploaded content
     */
    public function upload(ResourceInterface $file): string
    {
        $response = $this->client->putObject([
            'Bucket' => $this->bucket,
            'Key'    => $file->getHashName(),
            'Body'   => $file->getContents()
        ]);

        return $response['ObjectURL'];
    }

    public function remove(string $filename): bool
    {
        $result = $this->client->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $filename,
        ]);

        if ($result['DeleteMarker']) {
            return $result['DeleteMarker'];
        } else {
            throw new \Exception("Unable to delete.");
        }
    }

    public function startLargeFile(string $filename): string
    {
        $response = $this->client->createMultipartUpload([
            'Bucket' => $this->bucket,
            'Key' => $filename,
        ]);

        return $response['UploadId'];
    }

    public function uploadPart(string $filename, $data, string $fileId, int $partNumber): string
    {
        $result = $this->client->uploadPart([
            'Bucket'     => $this->bucket,
            'Key'        => $filename,
            'UploadId'   => $fileId,
            'PartNumber' => $partNumber,
            'Body'       => $data,
        ]);

        return $result['ETag'];
    }

    public function finishLargeFile(string $filename, string $fileId, array $parts)
    {
        $result = $this->client->completeMultipartUpload([
            'Bucket'   => $this->bucket,
            'Key'      => $filename,
            'UploadId' => $fileId,
            'MultipartUpload' => [
                'Parts' => $parts,
            ],
        ]);

        return $result['ObjectURL'];
    }

    public function deleteLargeFile(string $fileId)
    {
        $this->client->abortMultipartUpload([
            'Bucket' => $this->bucket,
            'UploadId' => $fileId,
        ]);
    }
}