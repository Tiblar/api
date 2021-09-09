<?php
namespace App\Service\Search;

use App\Entity\Analytics\ViewLog;
use App\Entity\User\User;
use App\Service\Post\Retrieve\AddonsBuilder;
use App\Service\Post\Retrieve\Formatter;
use App\Service\Post\RetrieveQueryBuilder;
use App\Structure\User\SanitizedUser;
use Doctrine\ORM\EntityManagerInterface;

class Video extends SearchBase
{
    public function __construct(EntityManagerInterface $em, AddonsBuilder $addonsBuilder, Formatter $formatter, RetrieveQueryBuilder $rqb)
    {
        parent::__construct($em, $addonsBuilder, $formatter, $rqb, SearchBase::$TYPE_VIDEO);
    }

    /**
     * @param $query
     * @param $limit
     * @param $nsfw
     * @param int $offset
     * @param int $period
     * @param string $type
     * @return array
     * @throws \Exception
     */
    public function popular($query, $limit, $offset = 0): array
    {
        $limit = intval($limit);

        $qb = $this->rqb->getQueryBuilder(false, $limit, $offset);

        $qb->andWhere('(p.reblog IS NULL)');
        $qb->andWhere('((p.title like :query) OR (p.body like :query) 
                        OR (:tag IN (SELECT t.title FROM App:Post\TagList t WHERE t.post = p.id)))');

        $qb->andWhere('(p.reblog IS NULL)');
        $qb->andWhere('(p.videoCategory IS NOT EMPTY)');
        $qb->andWhere('(p.title IS NOT NULL)');
        $qb->leftJoin('p.attachments', 'a');
        $qb->andWhere('(a.thumbnails IS NOT EMPTY)');
        $qb->having(
            $qb->expr()->gt(
                "(" .
                $this->em
                    ->createQueryBuilder()
                    ->select('COUNT(at.id)')
                    ->from('App:Media\Attachment', 'at')
                    ->where('at.post = p.id')
                    ->getDQL()
                . ")"
                ,
                0
            )
        );

        $qb->setParameter('query', '%' . $query . '%')
            ->setParameter('tag', $query);

        $qb->orderBy('p.favoritesCount', 'DESC');

        $ids = $qb->getQuery()->getArrayResult();

        if(!empty($ids)){
            $ids = array_column($ids, 'id');
        }

        $posts = $this->formatter->posts($ids);

        usort($posts, function($a, $b) {
            return $b->getFavoritesCount() - $a->getFavoritesCount();
        });

        $postIds = [];
        $userIds = [];
        foreach($posts as $post){
            $postIds[] = $post->getId();
            $userIds[$post->getId()] = $post->getAuthor()->getId();
        }

        $this->em->getRepository(ViewLog::class)
            ->addViews($postIds, $userIds, ViewLog::$VIEW_SOURCE_SEARCH, $this->addonsBuilder->getIpAddress(), ViewLog::$VIEW_TYPE_POST);

        return array_map(function ($post) {
            return $post->toArray();
        },  $posts);
    }
}
