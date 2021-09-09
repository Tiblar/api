<?php
namespace App\Service\Post\Retrieve;

use App\Entity\Analytics\ViewLog;
use App\Entity\User\User;
use App\Service\User\Block;
use App\Structure\Post\SanitizedMention;
use App\Structure\User\BlockStructure;
use App\Structure\User\FollowStructure;
use App\Service\User\Follow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class AddonsBuilder
{

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var Follow
     */
    private $follow;

    /**
     * @var Block
     */
    private $block;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(RequestStack $requestStack, Security $security, Follow $follow, Block $block, EntityManagerInterface $em)
    {
        $this->requestStack = $requestStack;
        $this->security = $security;
        $this->follow = $follow;
        $this->block = $block;
        $this->em = $em;
    }

    /**
     * Returns IP address
     *
     * @return string
     */
    public function getIpAddress(): string
    {
        $clientIp = $this->requestStack->getCurrentRequest()->getClientIp();

        $hasCloudflare = $this->requestStack->getCurrentRequest()->headers->has("X-Forwarded-For-Formerly-Chucks");

        $ipAddress = null;
        if($hasCloudflare){
            $ipAddress = $this->requestStack->getCurrentRequest()->headers->get("X-Forwarded-For-Formerly-Chucks");
        }

        if(is_null($ipAddress)){
            $ipAddress = $clientIp;
        }

        return $ipAddress;
    }

    public function getUserId()
    {
        $userId = null;
        if ($this->security->isGranted('ROLE_USER')) {
            $userId = $this->security->getToken()->getUser()->getId();
        }

        return $userId;
    }

    /**
     * Is staff API user
     *
     * @return bool
     */
    public function isStaff(): bool
    {
        if($this->security->isGranted("ROLE_STAFF_API")){
            return true;
        }

        return false;
    }

    /**
     * @return FollowStructure
     */
    public function getFollow(): FollowStructure
    {
        return $this->follow->get($this->getUserId());
    }

    /**
     * @return BlockStructure
     */
    public function getBlock(): BlockStructure
    {
        return $this->block->get($this->getUserId());
    }

    public function getPosts(array $ids): array
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select(
            'p', 'c', 'a', 'at', 'f', 't', 'tf', 'm', 'po',
            'r', 'ra', 'rat', 'rf', 'rm', 'rpo'
        );
        $qb->from('App:Post\Post', 'p');

        $qb->leftJoin('p.author', 'a')
            ->leftJoin('p.videoCategory', 'c')
            ->leftJoin('p.attachments', 'at')
            ->leftJoin('at.file', 'f')
            ->leftJoin('at.thumbnails', 't')
            ->leftJoin('t.file', 'tf')
            ->leftJoin('p.magnet', 'm')
            ->leftJoin('p.poll', 'po');

        $qb->leftJoin('p.reblog', 'r')
            ->leftJoin('r.author', 'ra')
            ->leftJoin('r.attachments', 'rat')
            ->leftJoin('rat.file', 'rf')
            ->leftJoin('r.magnet', 'rm')
            ->leftJoin('r.poll', 'rpo');

        $qb->where('p.id IN (:ids)');

        $qb->setParameter('ids', $ids);

        return $qb->getQuery()->getArrayResult();
    }

    public function getUsers(array $userIds): array
    {
        return $this->em->getRepository(User::class)
            ->findSanitizedUsers($userIds);
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getMentions(array $ids): array
    {
        $mentions = $this->em->createQueryBuilder()
            ->select('m')
            ->from('App:Post\Mention', 'm')
            ->where('m.postId IN (:ids) AND m.replyId IS NULL')
            ->setParameter('ids', $ids)
            ->getQuery()->getArrayResult();

        $users = $this->em->getRepository(User::class)
            ->findSanitizedUsers(array_column($mentions, 'userId'));

        $sanitized = [];
        foreach($mentions as $mention){
            $sanitizedMention = new SanitizedMention($mention);

            foreach($users as $user){
                if($user->getId() === $mention['userId']){
                    $sanitizedMention->setUser($user);
                    break;
                }
            }

            $sanitized[] = $sanitizedMention;
        }

        return $sanitized;
    }

    /**
     * @param array $ids
     * @return array
     */
    public function getViews(array $ids): array
    {
        $postViews = $this->em->createQueryBuilder()
            ->select('p.postId, SUM(p.views) as views')
            ->from('App:Analytics\PostAnalytics', 'p')
            ->where('p.postId IN (:ids)')
            ->setParameter('ids', $ids)
            ->groupBy('p.postId')
            ->getQuery()
            ->getArrayResult();

        $viewLogs = $this->em->createQueryBuilder()
            ->select('l.resourceId as postId, COUNT(l.resourceId) as views')
            ->from('App:Analytics\ViewLog', 'l')
            ->where('l.resourceId IN (:ids)')
            ->setParameter('ids', $ids)
            ->groupBy('l.resourceId')
            ->getQuery()
            ->getArrayResult();

        $views = [];
        foreach($ids as $id){
            $count = 0;

            $pViewsId = array_search($id, array_column($postViews, "postId"));

            if($pViewsId !== false && isset($postViews[$pViewsId])){
                $count += $postViews[$pViewsId]['views'];
            }

            $vLogId = array_search($id, array_column($viewLogs, "postId"));

            if($vLogId !== false && isset($viewLogs[$vLogId])){
                $count += $viewLogs[$vLogId]['views'];
            }

            $views[] = [
                'postId' => $id,
                'views' => $count,
            ];
        }

        return $views;
    }

    /**
     * @param string $postId
     * @return array
     */
    public function getReplyMentions(string $postId): array
    {
        return $this->em->createQueryBuilder()
            ->select('m')
            ->from('App:Post\Mention', 'm')
            ->where('m.postId = :postId AND m.replyId IS NOT NULL')
            ->setParameter('postId', $postId)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @param $ids
     * @return array
     */
    public function getTags(array $ids): array
    {
        return $this->em->createQueryBuilder()
            ->select('t')
            ->from('App:Post\TagList', 't')
            ->where('t.post IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()->getArrayResult();
    }

    /**
     * @param $ids
     * @return array
     */
    public function getPinsIds(array $ids): array
    {
        $pins = $this->em->createQueryBuilder()
            ->select('p')
            ->from('App:User\Addons\Pin', 'p')
            ->where('p.postId IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()->getArrayResult();

        return array_column($pins, 'postId');
    }

    /**
     * @param array $arr
     * @param array $ids
     * @return array
     */
    public function getAuthorIds(array $arr, array $ids = []): array
    {
        if(empty($arr)) return $ids;

        $ids = array_merge(
            array_column(array_column($arr, 'author'), 'id'),
            $ids
        );

        $next = [];
        if(!empty(array_column($arr, 'reblog'))){
            $next = array_column($arr, 'reblog');
        }

        if(!empty(array_column($arr, 'children'))){
            $next = array_column($arr, 'children');
        }

        return $this->getAuthorIds($next, $ids);
    }

    /**
     * @param array $posts
     * @param array $ids
     * @return array
     */
    public function getPostIds(array $posts, array $ids = []): array
    {
        if(empty($posts)) return $ids;

        $ids = array_merge(
            array_column($posts, 'id'),
            $ids
        );

        $reblogs = array_column($posts, 'reblog');

        return $this->getPostIds($reblogs, $ids);
    }

    /**
     * @return array
     */
    public function getReblogsIds() {
        $reblogs = $this->em->createQueryBuilder()
            ->select('r.id')
            ->from('App:Post\Post', 'p')
            ->leftJoin('p.reblog', 'r')
            ->where('p.reblog is not null and p.attachments is empty and p.body is null and p.author = :userId')
            ->setParameter('userId', $this->getUserId())
            ->getQuery()->getArrayResult();

        return array_column($reblogs, 'id');
    }

    /**
     * @return array
     */
    public function getFavoritesIds() {
        $favorites = $this->em->createQueryBuilder()
            ->select('f.postId')
            ->from('App:Post\Favorite', 'f')
            ->where('f.favoriter = :userId')
            ->setParameter('userId', $this->getUserId())
            ->getQuery()->getArrayResult();

        return array_column($favorites, 'postId');
    }
}