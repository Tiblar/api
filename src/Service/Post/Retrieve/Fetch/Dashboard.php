<?php
namespace App\Service\Post\Retrieve\Fetch;

use App\Entity\Analytics\ViewLog;
use App\Service\Post\Retrieve\AddonsBuilder;
use App\Service\Post\Retrieve\Formatter;
use App\Service\Post\RetrieveQueryBuilder;
use Doctrine\ORM\EntityManagerInterface;

class Dashboard
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

    public function newest($limit, $offset = 0)
    {
        $limit = intval($limit);

        $qb = $this->rqb->getQueryBuilder(false, $limit, $offset);

        $follow = $this->addonsBuilder->getFollow();

        $qb->andWhere('(p.author IN (:following) OR p.author = :userId)');

        $qb->setParameter('following', $follow->getFollowing());

        //$qb->orderBy('p.id', 'DESC');
        $qb->orderBy('p.timestamp', 'DESC');

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
            ->addViews($postIds, $userIds, ViewLog::$VIEW_SOURCE_DASHBOARD, $this->addonsBuilder->getIpAddress(), ViewLog::$VIEW_TYPE_POST);

        return array_map(function ($post) {
            return $post->toArray();
        },  $posts);
    }

    public function popular($limit, $offset = 0, $period = 1)
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

        $follow = $this->addonsBuilder->getFollow();

        $qb = $this->rqb->getQueryBuilder(false, $limit, $offset);

        $qb->andWhere('(p.author IN (:following) OR p.author = :userId)');

        if($period >= 0 && $period < 6){
            $qb->andWhere('(p.timestamp >= :date)');
            $qb->setParameter('date', $date);
        }

        $qb->setParameter('following', $follow->getFollowing());

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
            ->addViews($postIds, $userIds, ViewLog::$VIEW_SOURCE_DASHBOARD, $this->addonsBuilder->getIpAddress(), ViewLog::$VIEW_TYPE_POST);

        return array_map(function ($post) {
            return $post->toArray();
        },  $posts);
    }
}
