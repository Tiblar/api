<?php
namespace App\Service\Post\Retrieve\Fetch;

use App\Entity\Analytics\ViewLog;
use App\Service\Post\Retrieve\AddonsBuilder;
use App\Service\Post\Retrieve\Formatter;
use App\Service\Post\RetrieveQueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class Single
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
     * @var Security
     */
    private $security;

    /**
     * @var RetrieveQueryBuilder
     */
    private $rqb;

    public function __construct(EntityManagerInterface $em, AddonsBuilder $addonsBuilder,
                                Formatter $formatter, Security $security, RetrieveQueryBuilder $rqb)
    {
        $this->em = $em;
        $this->addonsBuilder = $addonsBuilder;
        $this->formatter = $formatter;
        $this->security = $security;
        $this->rqb = $rqb;
    }

    public function single($postId)
    {
        $qb = $this->rqb->getQueryBuilder(false, 1, 0);

        $qb->andWhere('(p.id = :postId OR p.oldUUID = :postId)');

        $qb->setParameter('postId', $postId);

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
            ->addViews($postIds, $userIds, ViewLog::$VIEW_SOURCE_DIRECT, $this->addonsBuilder->getIpAddress(), ViewLog::$VIEW_TYPE_POST);

        return array_map(function ($post) {
            return $post->toArray();
        },  $posts);
    }
}
