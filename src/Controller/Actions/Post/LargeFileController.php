<?php

namespace App\Controller\Actions\Post;

use App\Controller\ApiController;
use App\Entity\Media\File;
use App\Entity\Media\FileInit;
use App\Entity\Media\FilePart;
use App\Entity\User\ActionLog;
use App\Entity\User\User;
use App\Service\S3\Client;
use App\Service\Content\ContentException\InvalidException;
use App\Service\User\UserLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LargeFileController extends ApiController
{
    /**
     * @Route("/post/large-file", name="start_large_file", methods={"POST"})
     */
    public function start(Request $request, Client $blaze, UserLogger $logger)
    {
        $em = $this->getDoctrine()->getManager();

        $originalName = $request->request->get('file_name');
        $fileSize = intval($request->request->get('file_size'));
        $sha1 = $request->request->get('sha1_hash');

        if($fileSize <= 50000000){
            return $this->respondWithErrors([
                'file_size' => 'The file size must be greater than 50,000,000 bytes.'
            ], 'The file size must be greater than 0 bytes.');
        }

        $partCount = ceil($fileSize / 10000000);

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'token' => 'Invalid token.'
            ], 'Authentication error.', 401);
        }

        $since = new \DateTime();
        $since = $since->modify("-5 minutes");

        $count = $logger->count([ActionLog::$S3_CREATE_LARGE_FILE], $since, [
            'user_id' => $user->getId(),
        ]);

        if($count[ActionLog::$S3_CREATE_LARGE_FILE] > 15){
            return $this->respondWithErrors(
                [],
                'Please wait a few minutes requesting again.',
                429
            );
        }

        if($partCount < 2 || $partCount > 1000){
            return $this->respondWithErrors([
                'part_count' => 'Part count must be at least 2 and no more than 1,000.'
            ], null, 404);
        }

        $ext = pathinfo($originalName, PATHINFO_EXTENSION);

        $fileTypes = $this->getParameter('file_types');

        $found = false;
        $foundType = null;

        foreach($fileTypes as $type => $exts){
            foreach($exts as $typeExt){
                if($typeExt === $ext){
                    $foundType = $type;
                    $found = true;
                    break;
                }
            }
        }

        if(!$found || !$foundType){
            throw new InvalidException();
        }

        $fileSizes = $this->getParameter($user->isBoosted() ? 'file_sizes_boost' : 'file_sizes');

        if(!isset($fileSizes[$foundType])){
            throw new InvalidException();
        }

        $maxSize = $fileSizes[$foundType] / 1000;

        if($fileSize / 1000 / 1000 / 1000 > $maxSize){
            return $this->respondWithErrors([
                'file_size' => 'The maximum upload size is ' . $maxSize . ' GB for the given type.'
            ], 'You are trying to upload a file that is too large for the given type.');
        }

        if(!((bool) preg_match('/^[0-9a-f]{40}$/i', $sha1))){
            return $this->respondWithErrors([
                'sha1_hash' => 'Invalid sha1 hash.'
            ], null, 400);
        }


        $fileInit = new FileInit();
        $em->persist($fileInit);

        $fileInit->setUserId($this->getUser()->getId());
        $fileInit->setHash(hash('sha256', $fileInit->getId()));
        $fileInit->setExtension($ext);
        $fileInit->setOriginalName($originalName);
        $fileInit->setPartCount($partCount);
        $fileInit->setFileSize(0);
        $fileInit->setMaxFileSize($maxSize);

        $fileId = $blaze->startLargeFile($fileInit->getHash() . '.' . $fileInit->getExtension());

        $fileInit->setS3FileId($fileId);

        $em->flush();

        $logger->add($user, ActionLog::$S3_CREATE_LARGE_FILE);

        return $this->respond([
            'file_id' => $fileInit->getId(),
            'part_count' => $partCount
        ]);
    }

    /**
     * @Route("/post/large-file/{fileId}/{partNumber}", name="large_file_upload_segment", requirements={"partNumber" = "^([1-9][0-9]{0,2}|1000)$"}, methods={"POST"})
     */
    public function uploadSegment(Request $request, Client $s3, $fileId, $partNumber)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'token' => 'Invalid token.'
            ], 'Authentication error.', 401);
        }

        $filePartData = $request->files->get('file_part');

        if(!($filePartData instanceof UploadedFile)){
            return $this->respondWithErrors([
                'file_part' => 'File part is required.'
            ], null, 400);
        }

        if($filePartData->getSize() === 0){
            return $this->respondWithErrors([
                'file_part' => 'File part size must be more than 0 bytes.'
            ], null, 400);
        }

        $fileInit = $em->getRepository(FileInit::class)->findOneBy([
           'id' => $fileId,
           'userId' => $this->getUser()->getId(),
        ]);

        if(!($fileInit instanceof FileInit)){
            return $this->respondWithErrors([
                'id' => 'File not found.'
            ], null, 404);
        }

        $part = $em->getRepository(FilePart::class)->findOneBy([
            'fileInitId' => $fileId,
            'userId' => $this->getUser()->getId(),
            'part' => $partNumber
        ]);

        if($partNumber < 0 || $partNumber > $fileInit->getPartCount() || $part instanceof FilePart){
            return $this->respondWithErrors([
                'part_count' => 'Part count is invalid.'
            ], null, 404);
        }

        $part = new FilePart();

        $etag = $s3->uploadPart(
            $fileInit->getHash() . '.' . $fileInit->getExtension(),
            file_get_contents($filePartData->getRealPath()),
            $fileInit->getS3FileId(),
            $partNumber
        );

        $part->setUserId($this->getUser()->getId());
        $part->setFileInitId($fileInit->getId());
        $part->setETag($etag);
        $part->setPartSize($filePartData->getSize() / 1000 / 1000 / 1000);
        $part->setPart($partNumber);

        $em->persist($part);

        $fileInit->setFileSize($fileInit->getFileSize() + $part->getPartSize());

        $user->setStorage($user->getStorage() + $part->getPartSize());

        $em->flush();

        if($fileInit->getFileSize() > $fileInit->getMaxFileSize()){
            return $this->respondWithErrors([
                'file_part' => 'Size limit hit.'
            ], 'You are trying to upload a file that is too large for the given type.');
        }

        if($user->getStorage() > $user->getStorageLimit()){
            return $this->respondWithErrors([
                'storage' => 'Storage limit hit.'
            ], 'You are out of storage space. Please upgrade your account to increase storage limit.');
        }

        return $this->respond([], "File part processed.");
    }

    /**
     * @Route("/post/large-file/{fileId}/finish", name="finish_large_file", methods={"POST"})
     */
    public function finish(Request $request, Client $client, LoggerInterface $logger, $fileId)
    {
        $em = $this->getDoctrine()->getManager();

        $fileInit = $em->getRepository(FileInit::class)->findOneBy([
            'id' => $fileId,
            'userId' => $this->getUser()->getId(),
        ]);

        if(!($fileInit instanceof FileInit)){
            return $this->respondWithErrors([
                'id' => 'File not found.'
            ], null, 404);
        }

        $parts = $em->createQueryBuilder()
            ->select('p.part as PartNumber, p.ETag as ETag')
            ->from('App:Media\FilePart', 'p')
            ->where('p.fileInitId = :fileId AND p.userId = :userId')
            ->setParameter('fileId', $fileId)
            ->setParameter('userId', $this->getUser()->getId())
            ->groupBy('p.id')
            ->getQuery()
            ->getArrayResult();

        $count = count($parts);

        if($count < $fileInit->getPartCount()){
            return $this->respondWithErrors([
                'id' => 'This file is not fully uploaded.'
            ]);
        }

        try{
            $url = $client->finishLargeFile(
                $fileInit->getHash() . '.' . $fileInit->getExtension(),
                $fileInit->getS3FileId(),
                $parts,
            );
        }catch(\Exception $e) {
            $logger->debug($e->getMessage());
            return $this->respondWithErrors([
                'cdn' => "Something went wrong."
            ], null, 500);
        }

        $file = new File();
        $file->setHash($fileInit->getHash());
        $file->setExtension($fileInit->getExtension());
        $file->setFileSize($fileInit->getFileSize());
        $file->setHashName($fileInit->getHash() . '.' . $fileInit->getExtension());

        $em->persist($file);

        $fileInit->setStatus(FileInit::$FILE_INIT_FINISHED);
        $fileInit->setFileId($file->getId());

        $em->flush();

        return $this->respond([]);
    }

    /**
     * @Route("/post/large-file/{fileId}", name="delete_large_file", methods={"DELETE"})
     */
    public function delete(Request $request, Client $blaze, $fileId)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'token' => 'Invalid token.'
            ], 'Authentication error.', 401);
        }

        $fileInit = $em->getRepository(FileInit::class)->findOneBy([
            'id' => $fileId,
            'userId' => $this->getUser()->getId(),
        ]);

        if(!($fileInit instanceof FileInit)){
            return $this->respondWithErrors([
                'id' => 'File not found.'
            ], null, 404);
        }

        $blaze->deleteLargeFile($fileInit->getS3FileId());

        $em->getConnection()->beginTransaction();

        try {
            $em->createQueryBuilder()
                ->delete('App:Media\FileInit', 'f')
                ->where('f.id = :id')
                ->setParameter('id', $fileInit->getId())
                ->getQuery()->getResult();

            $em->createQueryBuilder()
                ->delete('App:Media\FilePart', 'p')
                ->where('p.fileInitId = :fileInitId')
                ->setParameter('fileInitId', $fileInit->getId())
                ->getQuery()->getResult();

            $em->createQueryBuilder()
                ->delete('App:Media\File', 'f')
                ->where('f.hash = :hash')
                ->setParameter('hash', $fileInit->getHash())
                ->getQuery()->getResult();

            $user->setStorage($user->getStorage() - $fileInit->getFileSize());

        }catch(\Exception $e){
            $em->getConnection()->rollback();
        }

        return $this->respond([]);
    }
}