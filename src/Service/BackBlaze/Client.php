<?php
namespace App\Service\BackBlaze;

class Client extends \obregonco\B2\Client {

    public function startLargeFile(array $options)
    {
        $bucketId = $this->getBucketFromName($options['BucketName'])->getId();

        return $this->request('POST', '/b2_start_large_file', [
            'json' => [
                'bucketId' => $bucketId,
                'fileName' => $options['FileName'],
                'contentType' => "b2/x-auto",
                'fileInfo' => [
                    'large_file_sha1' => $options['Sha1']
                ]
            ],
        ]);
    }

    public function uploadPart($data, string $fileId, int $partNumber)
    {
        $response = $this->request('POST', '/b2_get_upload_part_url', [
            'json' => [
                'fileId' => $fileId,
            ],
        ]);

        $uploadEndpoint = $response['uploadUrl'];
        $uploadAuthToken = $response['authorizationToken'];

        $this->request('POST', $uploadEndpoint, [
            'headers' => [
                'Authorization' => $uploadAuthToken,
                'X-Bz-Part-Number' => $partNumber,
                'Content-Length' => strlen($data),
                'X-Bz-Content-Sha1' => sha1($data),
            ],
            'body' => $data,
        ]);
    }

    public function finishLargeFile(string $fileId, array $hashParts)
    {
        $this->request('POST', '/b2_finish_large_file', [
            'json' => [
                'fileId' => $fileId,
                'partSha1Array' => $hashParts,
            ],
        ]);
    }


    public function deleteLargeFile(string $fileId)
    {
        $this->request('POST', '/b2_cancel_large_file', [
            'json' => [
                'fileId' => $fileId,
            ],
        ]);
    }
}