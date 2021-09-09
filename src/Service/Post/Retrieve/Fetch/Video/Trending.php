<?php
namespace App\Service\Post\Retrieve\Fetch\Video;

use App\Entity\Analytics\ViewLog;
use App\Service\Post\Retrieve\AddonsBuilder;
use App\Service\Post\Retrieve\Formatter;
use App\Service\Post\RetrieveQueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class Trending
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

    public function __construct(EntityManagerInterface $em, AddonsBuilder $addonsBuilder, Formatter $formatter, RetrieveQueryBuilder $rqb)
    {
        $this->em = $em;
        $this->addonsBuilder = $addonsBuilder;
        $this->formatter = $formatter;
        $this->rqb = $rqb;
    }

    public function trending($limit, $offset = 0, $period = 1)
    {
        switch($period){
            case 1:
                $days = 3;
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
            case 6:
                $days = 1000;
                break;
            default:
                $days = 3;
        }

        $date = new \DateTime("now");
        $date->modify("-$days day");

        $limit = intval($limit);

        if($limit === 0){
            $limit = 10;
        }

        $qb = $this->rqb->getQueryBuilder(false, $limit, $offset);
        $qb->andWhere('(p.reblog IS NULL)');
        $qb->andWhere('(p.nsfw = false)');
        $qb->andWhere('(p.title IS NOT NULL)');
        $qb->leftJoin('p.attachments', 'a');
        $qb->andWhere('(a.thumbnails IS NOT EMPTY)');

        if($period >= 0 && $period < 6){
            $qb->andWhere('(p.timestamp >= :date)');
            $qb->setParameter('date', $date);
        }

        $qb->andWhere('(p.reblog is null)');

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
            ->addViews($postIds, $userIds, ViewLog::$VIEW_SOURCE_TRENDING, $this->addonsBuilder->getIpAddress(), ViewLog::$VIEW_TYPE_POST);

        return array_map(function ($post) {
            return $post->toArray();
        },  $posts);
    }
}
