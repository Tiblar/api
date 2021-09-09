<?php
namespace App\Service\Recommend;

use App\Entity\User\User;
use App\Service\Post\Retrieve\Fetch\Multiple;
use App\Service\Post\Retrieve\Fetch\Video\Trending;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;

class Generator {

    private $em;
    private $tokenStorage;
    private $security;
    private $fetchPosts;
    private $fetchVideoTrending;

    public function __construct(EntityManagerInterface $em, TokenStorageInterface $securityToken, Security $security, Multiple $multiple, Trending $videoTrending)
    {
        $this->em = $em;
        $this->tokenStorage = $securityToken;
        $this->security = $security;
        $this->fetchPosts = $multiple;
        $this->fetchVideoTrending = $videoTrending;
    }

    public function interestingTags()
    {
        $tags = $this->em->createQueryBuilder()
            ->select('t')
            ->from('App:Post\Tag', 't')
            ->where('t.count > 3 AND t.nsfw = 0')
            ->orderBy('RAND()')
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult();

        return $this->format($tags);
    }

    public function trendingTags()
    {
        $timestamp = new \DateTime();
        $timestamp->modify("-2 days");

        $list = $this->em->createQueryBuilder()
            ->select('t.title, COUNT(t.title) as count')
            ->from('App:Post\TagList', 't')
            ->where('t.timestamp >= :timestamp')
            ->setParameter('timestamp', $timestamp)
            ->orderBy('count', 'DESC')
            ->groupBy('t.title')
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult();
        $list = array_column($list, 'title');

        $tags = $this->em->createQueryBuilder()
            ->select('t')
            ->from('App:Post\Tag', 't')
            ->where('t.title IN (:titles)')
            ->setParameter('titles', $list)
            ->orderBy('RAND()')
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult();

        return $this->format($tags);
    }

    public function people()
    {
        $qb = $this->em->createQueryBuilder()
            ->select('i.id')
            ->from('App:User\UserInfo', 'i')
            ->where('i.followerCount > 30 AND i.nsfw = 0');

      if($this->security->isGranted("ROLE_USER")){
          $qb->andWhere('i.id != :userId')
              ->setParameter('userId', $this->security->getUser()->getId());
      }

      $userIds = $qb->orderBy('i.followerCount', 'DESC')
          ->orderBy('RAND()')
          ->setMaxResults(4)
          ->getQuery()
          ->getArrayResult();
        $userIds = array_column($userIds, 'id');

        $sanitized = $this->em->getRepository(User::class)
            ->findSanitizedUsers($userIds);

        return array_map(function ($user){
            return $user->toArray();
        }, $sanitized);
    }

    public function posts()
    {
        return [];
    }

    public function marketplace()
    {

    }

    public function sidebarVideos(string $postId): array
    {
        $ids = $this->em->createQueryBuilder()
            ->select('h.postId, COUNT(h.postId) as count')
            ->from('App:Video\VideoHistory', 'h')
            ->andWhere('h.lastId = :postId')
            ->setParameter('postId', $postId)
            ->orderBy('count', 'DESC')
            ->groupBy('h.postId')
            ->setMaxResults(20)
            ->getQuery()
            ->getArrayResult();
        $ids = array_column($ids, "postId");

        $posts = $this->fetchPosts->multiple($ids);

        usort($posts, function ($a, $b) {
            return $a['views'] - $b['views'];
        });

        $fillCount = 20 - count($posts);

        if($fillCount <= 0){
            return $posts;
        }

        $fillPosts = $this->fetchVideoTrending->trending($fillCount + 10, 0, 6);

        $unique = [];
        foreach ($posts as $post) {
            if($post['id'] !== $postId){
                $unique[$post['id']] = $post;
            }
        }

        foreach ($fillPosts as $post) {
            if($post['id'] !== $postId){
                $unique[$post['id']] = $post;
            }
        }

        return array_values($unique);
    }

    private function format($tags) {
        $abbrevs = [12 => 'T', 9 => 'B', 6 => 'M', 3 => 'K', 0 => ''];

        foreach($tags as &$tag){
            foreach ($abbrevs as $exponent => $abbrev) {
                if (abs($tag['count']) >= pow(10, $exponent)) {
                    $display = $tag['count'] / pow(10, $exponent);
                    $decimals = ($exponent >= 3 && round($display) < 100) ? 1 : 0;
                    $tag['count'] = number_format($display, $decimals).$abbrev;
                    break;
                }
            }
        }

        return $tags;
    }
}
