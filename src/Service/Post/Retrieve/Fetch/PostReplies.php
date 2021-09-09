<?php
namespace App\Service\Post\Retrieve\Fetch;

use App\Service\Post\Retrieve\AddonsBuilder;
use App\Service\Post\Retrieve\Formatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class PostReplies
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

    public function __construct(EntityManagerInterface $em, AddonsBuilder $addonsBuilder, Formatter $formatter)
    {
        $this->em = $em;
        $this->addonsBuilder = $addonsBuilder;
        $this->formatter = $formatter;
    }

    public function get($postId)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('r', 'a', 'c', 'ca', 'cc', 'caa', 'ccc', 'caca', 'cccc');
        $qb->from('App:Post\Reply', 'r');

        $qb->leftJoin('r.author', 'a')
            ->leftJoin('r.children', 'c')
            ->leftJoin('c.author', 'ca')
            ->leftJoin('c.children', 'cc')
            ->leftJoin('cc.author', 'caa')
            ->leftJoin('cc.children', 'ccc')
            ->leftJoin('ccc.author', 'caca')
            ->leftJoin('ccc.children', 'cccc');

        $qb->where('(r.post = :postId AND r.depth = 0)');
        $qb->setParameter('postId', $postId);

        $qb->orderBy('r.timestamp', 'ASC');


        $data = $qb->getQuery()->getArrayResult();

        $replies = $this->formatter->replies($data, $postId);

        return array_map(function ($post) {
            return $post->toArray();
        },  $replies);
    }
}
