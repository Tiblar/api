<?php
namespace App\Service\Post\Retrieve\Fetch;

use App\Service\Post\Retrieve\AddonsBuilder;
use App\Service\Post\Retrieve\Formatter;
use App\Service\Post\RetrieveQueryBuilder;
use Doctrine\ORM\EntityManagerInterface;

class Staff
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

    public function posts($limit, $afterId = 0)
    {
        $limit = intval($limit);

        if($limit < 100 || $limit > 500){
            $limit = 100;
        }

        if(!ctype_digit($afterId)){
            $afterId = 0;
        }

        $qb = $this->rqb->getQueryBuilder(false, $limit, 0, true);

        $qb->andWhere('(CAST(p.id) > :afterId)');
        $qb->andWhere('(p.reblog is null)');
        $qb->setParameter('afterId', $afterId);

        $qb->orderBy('char_length(p.id)', 'ASC');
        $qb->orderBy('p.id', 'ASC');
        //$qb->orderBy('p.timestamp', 'ASC');

        $ids = $qb->getQuery()->getArrayResult();
        //print_r($ids);

        if(!empty($ids)){
            $ids = array_column($ids, 'id');
        }

        $posts = $this->formatter->posts($ids);

        usort($posts, function($a, $b) {
            return $a->getId() - $b->getId();
        });

        return array_map(function ($post) {
            return $post->toArray();
        },  $posts);
    }

    /**
     * @param $limit
     * @param int $afterId
     *
     * @return array
     * @throws \Exception
     */
    public function postReports($limit, $afterId = 0)
    {
        $limit = intval($limit);

        if($limit < 100 || $limit > 500){
            $limit = 100;
        }

        if(!ctype_digit($afterId)){
            $afterId = 0;
        }

        $qb = $this->em->createQueryBuilder();
        $qb->select('r');
        $qb->from('App:Report\PostReport', 'r');
        $qb->where('(r.id > :afterId)');
        $qb->setParameter('afterId', $afterId);
        $reports = $qb->getQuery()->getArrayResult();
        $reportIds = array_column($reports, 'postId');

        $qb = $this->rqb->getQueryBuilder(false, $limit, 0, true);

        $qb->andWhere('(p.id IN (:reports))');
        $qb->andWhere('(p.reblog is null)');
        $qb->setParameter('reports', $reportIds);

        $qb->orderBy('char_length(p.id)', 'ASC');
        $qb->orderBy('p.id', 'ASC');
        //$qb->orderBy('p.timestamp', 'ASC');

        $ids = $qb->getQuery()->getArrayResult();

        if(!empty($ids)){
            $ids = array_column($ids, 'id');
        }

        $posts = $this->formatter->posts($ids);

        usort($posts, function($a, $b) {
            return $a->getId() - $b->getId();
        });

        $posts = array_map(function ($post) {
            return $post->toArray();
        },  $posts);


        $reports = array_map(function ($report) {
            return [
                'id' => $report['id'],
                'user_id' => $report['userId'],
                'post_id' => $report['postId'],
                'timestamp' => $report['timestamp']->format('c'),
            ];
        },  $reports);

        return [
            'reports' => $reports,
            'posts' => $posts,
        ];
    }
}
