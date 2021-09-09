<?php
namespace App\Service\Search;

use App\Entity\Analytics\ViewLog;
use App\Service\Post\Retrieve\AddonsBuilder;
use App\Service\Post\Retrieve\Formatter;
use App\Service\Post\RetrieveQueryBuilder;
use Doctrine\ORM\EntityManagerInterface;

class Social extends SearchBase
{
   public function __construct(EntityManagerInterface $em, AddonsBuilder $addonsBuilder, Formatter $formatter, RetrieveQueryBuilder $rqb)
   {
       parent::__construct($em, $addonsBuilder, $formatter, $rqb, SearchBase::$TYPE_SOCIAL);
   }

    /**
     * @param $query
     * @param $limit
     * @param int $offset
     * @param bool $nsfw
     * @param string $type
     * @return array
     * @throws \Exception
     */
    public function newest($query, $limit, $nsfw, $type = "all", $offset = 0): array
    {
        $limit = intval($limit);

        if(!in_array($type, ["all", "media", "text"])){
            $type = "all";
        }

        $qb = $this->rqb->getQueryBuilder($nsfw, $limit, $offset);

        $qb->andWhere('(p.reblog IS NULL)');
        $qb->andWhere('((p.title like :query) OR (p.body like :query) 
                        OR (:tag IN (SELECT t.title FROM App:Post\TagList t WHERE t.post = p.id)))');

        if($type === "media"){
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
        }

        if($type === "text"){
            $qb->having(
                $qb->expr()->eq(
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
        }

        $qb->setParameter('query', '%' . $query . '%')
            ->setParameter('tag', $query);

        $qb->orderBy('p.id', 'DESC');

        $ids = $qb->getQuery()->getArrayResult();

        if(!empty($ids)){
            $ids = array_column($ids, 'id');
        }

        $posts = $this->formatter->posts($ids);

        usort($posts, function($a, $b) {
            return $b->getId() - $a->getId();
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
    public function popular($query, $limit, $nsfw, $type = "all", $offset = 0, $period = 1): array
    {
        $days = 1;
        switch($period){
            case 1:
                $days = 1;
                break;
            case 2:
                $days = 7;
                break;
            case 3:
                $days = 30;
                break;
            case 4:
                $days = 180;
                break;
            case 5:
                $days = 365;
                break;
            default:
                $days = 1;
        }

        $date = new \DateTime("now");
        $date->modify("-$days day");

        $limit = intval($limit);

        if(!in_array($type, ["all", "media", "text"])){
            $type = "all";
        }

        $qb = $this->rqb->getQueryBuilder($nsfw, $limit, $offset);

        $qb->andWhere('(p.reblog IS NULL)');
        $qb->andWhere('((p.title like :query) OR (p.body like :query) 
                        OR (:tag IN (SELECT t.title FROM App:Post\TagList t WHERE t.post = p.id)))');

        if($type === "media"){
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
        }

        if($type === "text"){
            $qb->having(
                $qb->expr()->eq(
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
        }

        if($period >= 0 && $period < 6){
            $qb->andWhere('(p.timestamp >= :date)');
            $qb->setParameter('date', $date);
        }

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
