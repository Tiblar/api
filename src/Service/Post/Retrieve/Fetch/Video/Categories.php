<?php
namespace App\Service\Post\Retrieve\Fetch\Video;

use App\Entity\Analytics\ViewLog;
use App\Service\Post\Retrieve\AddonsBuilder;
use App\Service\Post\Retrieve\Formatter;
use App\Service\Post\RetrieveQueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class Categories
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var AddonsBuilder
     */
    private $addonsBuilder;

    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @var RetrieveQueryBuilder
     */
    private $rqb;

    public function __construct(EntityManagerInterface $em, AddonsBuilder $addonsBuilder,
                                Formatter $formatter, RetrieveQueryBuilder $rqb)
    {
        $this->em = $em;
        $this->addonsBuilder = $addonsBuilder;
        $this->formatter = $formatter;
        $this->rqb = $rqb;
    }

    public function newest($category, $limit, $offset = 0, $notInIds = [])
    {
        if(!is_string($category)){
            return [];
        }

        $limit = intval($limit);
        $offset = intval($offset);

        if($limit === 0){
            $limit = 16;
        }

        $qb = $this->rqb->getQueryBuilder(false, $limit, $offset);

        $qb->andWhere('(p.reblog IS NULL)');
        $qb->andWhere('(p.title IS NOT NULL)');
        $qb->andWhere('(p.nsfw = false)');
        $qb->leftJoin('p.attachments', 'a');
        $qb->leftJoin('p.videoCategory', 'c');
        $qb->andWhere('(a.thumbnails IS NOT EMPTY)');

        $qb->andWhere('(:category = c.id)');

        if(!empty($notInIds)){
            $qb->andWhere('(p.id NOT IN (:notInIds))');
            $qb->setParameter('notInIds', $notInIds);
        }

        //$qb->orderBy('p.id', 'DESC');
        $qb->orderBy('p.timestamp', 'DESC');

        $qb->setParameter('category', $category);

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
            ->addViews($postIds, $userIds, ViewLog::$VIEW_SOURCE_NEWEST, $this->addonsBuilder->getIpAddress(), ViewLog::$VIEW_TYPE_POST);

        return array_map(function ($post) {
            return $post->toArray();
        },  $posts);
    }

    public function trending($category, $limit, $offset = 0)
    {
        if(!is_string($category)){
            return [];
        }

        $limit = intval($limit);
        $offset = intval($offset);

        if($limit === 0){
            $limit = 16;
        }

        $qb = $this->rqb->getQueryBuilder(false, $limit, $offset);

        $qb->andWhere('(p.reblog IS NULL)');
        $qb->andWhere('(p.title IS NOT NULL)');
        $qb->andWhere('(p.nsfw = false)');
        $qb->leftJoin('p.attachments', 'a');
        $qb->leftJoin('p.videoCategory', 'c');
        $qb->andWhere('(a.thumbnails IS NOT EMPTY)');

        $qb->andWhere('(:category = c.id)');

        $qb->orderBy('p.favoritesCount', 'DESC');

        $qb->setParameter('category', $category);

        $ids = $qb->getQuery()->getArrayResult();

        if(!empty($ids)){
            $ids = array_column($ids, 'id');
        }

        $newest = $this->newest($category, $limit, $offset, $ids);

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
            ->addViews($postIds, $userIds, ViewLog::$VIEW_SOURCE_NEWEST, $this->addonsBuilder->getIpAddress(), ViewLog::$VIEW_TYPE_POST);

        $posts = array_map(function ($post) {
            return $post->toArray();
        },  $posts);

        if(count($posts) > $limit){
            $posts = array_merge($posts, $newest);
            shuffle($posts);
        }

        return array_slice($posts, 0, $limit);
    }
}
