<?php
declare(strict_types=1);

namespace App\Entity\Media;

use App\Structure\Media\SanitizedAttachment;
use App\Structure\Media\SanitizedMagnet;
use App\Structure\Media\SanitizedPoll;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping;

class MediaRepository extends EntityRepository
{
    public function findSanitizedAttachments(array $ids): array
    {
        $attachments = $this->_em->createQueryBuilder()
            ->select('a, f', 'p.id as postId')
            ->from('App:Media\Attachment', 'a')
            ->leftJoin('a.file', 'f')
            ->leftJoin('a.post', 'p')
            ->where('a.post in (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()->getArrayResult();

        $sanitized = [];
        foreach($attachments as $attachment){
            $sanitized[] = new SanitizedAttachment(
                array_merge(['postId' => $attachment['postId']], $attachment[0])
            );
        }

        return $sanitized;
    }

    public function findSanitizedPolls(array $ids, ?string $userId = null): array
    {
        $polls = $this->_em->createQueryBuilder()
            ->select('p')
            ->from('App:Media\Poll', 'p')
            ->where('p.postId in (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()->getArrayResult();

        $votes = [];
        if(!is_null($userId)){
            $votes = $this->_em->createQueryBuilder()
                ->select('v')
                ->from('App:Media\PollVote', 'v')
                ->where('v.postId in (:ids)')
                ->andWhere('v.userId = :userId')
                ->setParameter('userId', $userId)
                ->setParameter('ids', $ids)
                ->getQuery()->getArrayResult();
        }

        $sanitized = [];
        foreach($polls as $poll){
            $sanitizedPoll = new SanitizedPoll(
                $poll
            );

            $index = array_search($poll['postId'], array_column($votes, "postId"));

            if($index !== false){
                $myVote = $votes[$index];

                $sanitizedPoll->setMyVote([
                    'id' => $myVote['id'],
                    'option' => $myVote['choice']
                ]);
            }

            if(isset($poll['o1']) && !is_null($poll['o1'])){
                $sanitizedPoll->addOption([
                    'title' => $poll['o1'],
                    'votes_count' => $poll['o1VotesCount'],
                ]);
            }

            if(isset($poll['o2']) && !is_null($poll['o1'])){
                $sanitizedPoll->addOption([
                    'title' => $poll['o2'],
                    'votes_count' => $poll['o2VotesCount'],
                ]);
            }

            if(isset($poll['o3']) && !is_null($poll['o3'])){
                $sanitizedPoll->addOption([
                    'title' => $poll['o3'],
                    'votes_count' => $poll['o3VotesCount'],
                ]);
            }

            if(isset($poll['o4']) && !is_null($poll['o4'])){
                $sanitizedPoll->addOption([
                    'title' => $poll['o4'],
                    'votes_count' => $poll['o4VotesCount'],
                ]);
            }

            $sanitized[] = $sanitizedPoll;
        }

        return $sanitized;
    }

    public function findSanitizedMagnets(array $ids)
    {
        $magnets = $this->_em->createQueryBuilder()
            ->select('m')
            ->from('App:Media\Magnet', 'm')
            ->where('m.postId in (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()->getArrayResult();

        $sanitized = [];
        foreach($magnets as $magnet){
            $sanitizedMagnet = new SanitizedMagnet($magnet);

            $sanitized[] = $sanitizedMagnet;
        }

        return $sanitized;
    }

    public function findUnusedFile(string $hash): ?File
    {
        $file = $this->_em->createQueryBuilder()
            ->select('m')
            ->from('App:Media\File', 'f')
            ->where('f.hash = :hash')
            ->setParameter('hash', $hash)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if(!$file INSTANCEOF File){
            return null;
        }

        $isUsed = $this->_em->createQueryBuilder()
            ->select('m')
            ->from('App:Media\File', 'f')
            ->where('f.hash = :hash')
            ->setParameter('hash', $hash)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if($isUsed){
            return null;
        }

        return $file;
    }
}
