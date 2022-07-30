<?php
namespace App\Service\Post\Retrieve\Fetch;

use App\Entity\Analytics\ViewLog;
use App\Entity\Post\Post;
use App\Entity\User\Addons\Pin;
use App\Service\Post\Retrieve\AddonsBuilder;
use App\Service\Post\Retrieve\Formatter;
use App\Service\Post\RetrieveQueryBuilder;
use App\Structure\Post\SanitizedPost;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class Profile
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
                                Formatter $formatter, RetrieveQueryBuilder  $rqb)
    {
        $this->em = $em;
        $this->addonsBuilder = $addonsBuilder;
        $this->formatter = $formatter;
        $this->rqb = $rqb;
    }

    public function newest($userId, $limit, $offset = 0, $query = null)
    {
        if(is_null($limit)){
            $limit = 10;
        }

        $limit = intval($limit);
        $offset = intval($offset);

        if($query == ''){
            $query = null;
        }

        $pinned = $this->em->getRepository(Pin::class)->findOneBy([
            'userId' => $userId,
        ]);

        if($pinned instanceof Pin && $offset === 0){
            $limit -= 1;
        }

        $qb = $this->rqb->getQueryBuilder(false, $limit, $offset);
        // post_user_id
        $qb->andWhere('(p.author = :profileId)');

        if(!is_null($query)){
            $qb->andWhere('((p.title like :query) OR (p.body like :query)
                        OR (:tag IN (SELECT t.title FROM App:Post\TagList t WHERE t.post = p.id)))');
            $qb->setParameter('query', '%' . $query . '%');
            $qb->setParameter('tag', $query);
        }

        if($pinned instanceof Pin){
            $qb->andWhere('(p.id != :pin)');
            $qb->setParameter('pin', $pinned->getPostId());
        }

        $qb->setParameter('profileId', $userId);

        // we're going to resort these anyways
        // but we are applying a limit...
        // and rqb requires a limit
        //$qb->orderBy('p.id', 'DESC');
        $qb->orderBy('p.timestamp', 'DESC');

        $ids = $qb->getQuery()->getArrayResult();

        if(!empty($ids)){
            $ids = array_column($ids, 'id');
        }

        if($pinned instanceof Pin && $offset === 0){
            $ids = array_merge($ids, [$pinned->getPostId()]);
        }

        $posts = $this->formatter->posts($ids);

        usort($posts, function($a, $b) {
            $aid = $a->getId();
            $bid = $b->getId();
            if (strlen($aid) > strlen($bid)) return -1;  // aid is bigger
            if (strlen($aid) < strlen($bid)) return 1; // bid is bigger
            // same size, then numerically compare
            // if b is bigger 1
            // if a is bigger -1
            return $bid - $aid;
        });

        $postIds = [];
        $userIds = [];
        foreach($posts as $post){
            $postIds[] = $post->getId();
            $userIds[$post->getId()] = $post->getAuthor()->getId();
        }

        $this->em->getRepository(ViewLog::class)
            ->addViews($postIds, $userIds, ViewLog::$VIEW_SOURCE_PROFILE, $this->addonsBuilder->getIpAddress(), ViewLog::$VIEW_TYPE_POST);

        $posts = array_map(function ($post) {
            return $post->toArray();
        }, $posts);

        if(!$pinned instanceof Pin){
            return $posts;
        }

        $key = array_search($pinned->getPostId(), array_column($posts, 'id'));

        if($key === false){
            return $posts;
        }

        $posts = array_merge([$key => $posts[$key]], $posts);
        unset($posts[$key + 1]);
        $posts = array_values($posts);

        return $posts;
    }

    public function videos($userId, $limit, $offset = 0, $query = null)
    {
        if(is_null($limit)){
            $limit = 100;
        }
        if($limit > 100){
            $limit = 100;
        }

        if($limit < 10){
            $limit = 10;
        }

        $limit = intval($limit);
        $offset = intval($offset);

        if($query == ''){
            $query = null;
        }

        $qb = $this->rqb->getQueryBuilder(false, $limit, $offset);

        $qb->andWhere('(p.author = :profileId)');
        $qb->andWhere('(p.reblog IS NULL)');
        //$qb->andWhere('(p.videoCategory IS NOT EMPTY)');
        $qb->andWhere('(p.title IS NOT NULL)');
        $qb->leftJoin('p.attachments', 'a');
        $qb->andWhere('(a.thumbnails IS NOT EMPTY)');

        if(!is_null($query)){
            $qb->andWhere('((p.title like :query) OR (p.body like :query)
                        OR (:tag IN (SELECT t.title FROM App:Post\TagList t WHERE t.post = p.id)))');
            $qb->setParameter('query', '%' . $query . '%');
            $qb->setParameter('tag', $query);
        }

        $qb->setParameter('profileId', $userId);

        //$qb->orderBy('p.id', 'DESC');
        $qb->orderBy('p.timestamp', 'DESC');

        $ids = $qb->getQuery()->getArrayResult();

        if(!empty($ids)){
            $ids = array_column($ids, 'id');
        }

        $posts = $this->formatter->posts($ids);

        usort($posts, function($a, $b) {
            $aid = $a->getId();
            $bid = $b->getId();
            if (strlen($aid) > strlen($bid)) return -1;  // aid is bigger
            if (strlen($aid) < strlen($bid)) return 1; // bid is bigger
            // same size, then numerically compare
            // if b is bigger 1
            // if a is bigger -1
            return $bid - $aid;
        });

        return array_map(function ($post) {
            return $post->toArray();
        },  $posts);
    }

    public function socialLikes($userId, $limit, $offset = 0)
    {
        $limit = intval($limit);

        $qb = $this->em->createQueryBuilder();
        $qb->select('f.postId as id');
        $qb->from('App:Post\Favorite', 'f');
        $qb->where('(f.favoriter = :profileId)');
        $qb->setParameter('profileId', $userId);

        $ids = $qb->getQuery()->getArrayResult();

        $ids = array_column($ids, 'id');

        $qb = $this->rqb->getQueryBuilder(false, $limit, $offset);

        $qb->andWhere('(p.id IN (:ids))');
        $qb->setParameter('ids', $ids);

        //$qb->orderBy('p.id', 'DESC');
        $qb->orderBy('p.timestamp', 'DESC');

        $ids = $qb->getQuery()->getArrayResult();

        if(!empty($ids)){
            $ids = array_column($ids, 'id');
        }

        $posts = $this->formatter->posts($ids);

        usort($posts, function($a, $b) {
            $aid = $a->getId();
            $bid = $b->getId();
            if (strlen($aid) > strlen($bid)) return -1;  // aid is bigger
            if (strlen($aid) < strlen($bid)) return 1; // bid is bigger
            // same size, then numerically compare
            // if b is bigger 1
            // if a is bigger -1
            return $bid - $aid;
        });

        return array_map(function ($post) {
            return $post->toArray();
        },  $posts);
    }

    public function videoLikes($userId, $limit, $offset = 0)
    {
        $limit = intval($limit);

        $qb = $this->em->createQueryBuilder();
        $qb->select('f.postId as id');
        $qb->from('App:Post\Favorite', 'f');
        $qb->where('(f.favoriter = :profileId)');
        $qb->setParameter('profileId', $userId);

        $ids = $qb->getQuery()->getArrayResult();

        $ids = array_column($ids, 'id');

        $qb = $this->rqb->getQueryBuilder(false, $limit, $offset);

        $qb->andWhere('(p.id IN (:ids))');
        $qb->andWhere('(p.reblog IS NULL)');
        $qb->andWhere('(p.title IS NOT NULL)');
        $qb->andWhere('(p.nsfw = false)');
        $qb->leftJoin('p.attachments', 'a');
        $qb->andWhere('(a.thumbnails IS NOT EMPTY)');
        $qb->setParameter('ids', $ids);

        //$qb->orderBy('p.id', 'DESC');
        $qb->orderBy('p.timestamp', 'DESC');

        $ids = $qb->getQuery()->getArrayResult();

        if(!empty($ids)){
            $ids = array_column($ids, 'id');
        }

        $posts = $this->formatter->posts($ids);

        usort($posts, function($a, $b) {
            $aid = $a->getId();
            $bid = $b->getId();
            if (strlen($aid) > strlen($bid)) return -1;  // aid is bigger
            if (strlen($aid) < strlen($bid)) return 1; // bid is bigger
            // same size, then numerically compare
            // if b is bigger 1
            // if a is bigger -1
            return $bid - $aid;
        });

        return array_map(function ($post) {
            return $post->toArray();
        },  $posts);
    }
}
