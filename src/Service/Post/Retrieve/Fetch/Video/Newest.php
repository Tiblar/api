<?php
namespace App\Service\Post\Retrieve\Fetch\Video;

use App\Entity\Analytics\ViewLog;
use App\Service\Post\Retrieve\AddonsBuilder;
use App\Service\Post\Retrieve\Formatter;
use App\Service\Post\RetrieveQueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class Newest
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
        $offset = intval($offset);

        if($limit === 0){
            $limit = 10;
        }

        $qb = $this->rqb->getQueryBuilder(false, $limit, $offset);

        $qb->andWhere('(p.reblog IS NULL)');
        $qb->andWhere('(p.title IS NOT NULL)');
        $qb->andWhere('(p.nsfw = false)');
        $qb->leftJoin('p.attachments', 'a');
        $qb->andWhere('(a.thumbnails IS NOT EMPTY)');

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
            ->addViews($postIds, $userIds, ViewLog::$VIEW_SOURCE_NEWEST, $this->addonsBuilder->getIpAddress(), ViewLog::$VIEW_TYPE_POST);

        return array_map(function ($post) {
            return $post->toArray();
        },  $posts);
    }
}
