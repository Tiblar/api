<?php
namespace App\Service\Search;

use App\Entity\User\User;
use App\Service\Post\Retrieve\AddonsBuilder;
use App\Service\Post\Retrieve\Formatter;
use App\Service\Post\RetrieveQueryBuilder;
use Doctrine\ORM\EntityManagerInterface;

abstract class SearchBase {
    static string $TYPE_SOCIAL = "TYPE_SOCIAL";
    static string $TYPE_VIDEO = "TYPE_VIDEO";

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var AddonsBuilder
     */
    protected $addonsBuilder;

    /**
     * @var Formatter
     */
    protected $formatter;

    /**
     * @var RetrieveQueryBuilder
     */
    protected $rqb;

    /**
     * @var string
     */
    protected string $type;

    public function __construct(EntityManagerInterface $em, AddonsBuilder $addonsBuilder,
                                Formatter $formatter, RetrieveQueryBuilder $rqb, string $type)
    {
        $this->em = $em;
        $this->addonsBuilder = $addonsBuilder;
        $this->formatter = $formatter;
        $this->rqb = $rqb;
        $this->type = $type;
    }

    /**
     * @param $query
     * @return array
     */
    public function profile($query): array
    {
        if(is_null($query) || empty($query)){
            return [];
        }

        $follow = $this->addonsBuilder->getFollow();
        $block = $this->addonsBuilder->getBlock();

        $blocked = empty($block->getBlocking()) ? [1, 2] : $block->getBlocking();
        //$followed = empty($follow->getFollowing()) ? [1, 2] : $follow->getFollowing();
        // i.id NOT IN (:following) AND
        $userIds = $this->em->createQuery("SELECT i.id as author FROM App:User\UserInfo i
                                    WHERE
                                     i.id NOT IN (:blocking)
                                        AND ((i.username like :search AND i.nsfw = false)
                                        OR (i.username = :query))
                                    ORDER BY i.followerCount DESC")
            ->setParameter('search', $query . '%')
            ->setParameter('query', $query)
            ->setParameter('blocking', $blocked)
            //->setParameter('following', $followed)
            ->setMaxResults(6)
            ->getArrayResult();

        $users = $this->em->getRepository(User::class)
            ->findSanitizedUsers(array_column($userIds, 'author'), $follow, $block);

        $profiles = array_map(function ($user) {
            return $user->toArray();
        }, $users);

        return $profiles;
    }

    /**
     * @param string $query
     * @param string|null $userId
     * @return array
     */
    public function tags(string $query, ?string $userId = null): array
    {
        if(empty($query)){
            return [];
        }

        $qb = $this->em->createQueryBuilder()
            ->select("t as tag")
            ->from("App:Post\Tag", "t")
            ->where(" t.title like :search AND t.nsfw = false")
            ->orderBy("t.count", "DESC")
            ->setParameter('search', $query . '%')
            ->setMaxResults(4);

        if(!is_null($userId)){
            $qb->andHaving(
                $qb->expr()->gt(
                    "(" .
                    $this->em
                        ->createQueryBuilder()
                        ->select('COUNT(l)')
                        ->from('App:Post\TagList', 'l')
                        ->where('l.userId = :userId')
                        ->andWhere('l.tag = t.id')
                        ->getDQL()
                    . ")"
                    ,
                    0
                )
            );

            $qb->setParameter('userId', $userId);
        }

        if($this->type === static::$TYPE_VIDEO){
            $qb->andHaving(
                $qb->expr()->gt(
                    "(" .
                    $this->em
                        ->createQueryBuilder()
                        ->select('COUNT(p)')
                        ->from('App:Post\Post', 'p')
                        ->where('(p.reblog IS NULL)')
                        ->andWhere('(t.id IN (SELECT tp.tag FROM App:Post\TagList tp WHERE tp.post = p.id))')
                        ->andWhere('(p.reblog IS NULL)')
                        ->andWhere('(p.videoCategory IS NOT EMPTY)')
                        ->andWhere('(p.title IS NOT NULL)')
                        ->leftJoin('p.attachments', 'a')
                        ->andWhere('(a.thumbnails IS NOT EMPTY)')
                        ->getDQL()
                    . ")"
                    ,
                    0
                )
            );
        }

        $tagList = $qb->getQuery()->getArrayResult();
        return array_column($tagList, 'tag');
    }

    /**
     * @param string $query
     * @param string|null $userId
     * @return array
     */
    function titles(string $query, ?string $userId = null): array
    {
        $qb = $this->rqb->getQueryBuilder(false, 3, 0);
        $qb->select('p.title');
        $qb->andWhere('p.title like :query');
        $qb->setParameter('query', '%' . $query . '%');
        $qb->distinct();

        if(!is_null($userId)){
            $qb->andWhere('p.author = :userId');

            $qb->setParameter('userId', $userId);
        }

        if($this->type === static::$TYPE_VIDEO){
            $qb->andWhere('(p.reblog IS NULL)');
            $qb->andWhere('(p.nsfw = false)');
            $qb->andWhere('(p.title IS NOT NULL)');
            $qb->leftJoin('p.attachments', 'a');
            $qb->andWhere('(a.thumbnails IS NOT EMPTY)');
        }

        $qb->setMaxResults(3);

        $posts = $qb->getQuery()->getArrayResult();

        return array_column($posts, 'title');
    }
}