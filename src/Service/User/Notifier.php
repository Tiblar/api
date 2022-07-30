<?php
namespace App\Service\User;

use App\Entity\User\Addons\Notification;
use App\Entity\User\User;
use App\Service\Post\Retrieve\Fetch\Multiple;
use App\Structure\User\SanitizedNotification;
use App\Structure\User\SanitizedUser;
use Doctrine\ORM\EntityManagerInterface;

class Notifier
{
    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    /**
     * @var Follow $follow
     */
    private $follow;

    /**
     * @var Block $block
     */
    private $block;

    /**
     * @var Multiple $multiple
     */
    private $multiple;

    public function __construct(EntityManagerInterface $em, Follow $follow, Block $block, Multiple $multiple)
    {
        $this->em = $em;
        $this->follow = $follow;
        $this->block = $block;
        $this->multiple = $multiple;
    }

    public function add(?User $causer, string $targetUser, string $type, ?string $post = null, ?string $message = null): void
    {
        if($causer INSTANCEOF User && $targetUser === $causer->getId()){
            return;
        }

        if(!$causer INSTANCEOF User && !in_array($type, [Notification::$TYPE_SYSTEM])){
            throw new \Exception("Causer argument required.");
        }

        if(in_array($type, [Notification::$TYPE_FOLLOW, Notification::$TYPE_UNFOLLOW])){
            $post = null;
        }

        if(in_array($type, [
            Notification::$TYPE_FOLLOW, Notification::$TYPE_UNFOLLOW,
            Notification::$TYPE_MENTION, Notification::$TYPE_REPLY_MENTION
        ])){
            $since = new \DateTime();
            $since->modify("-10 minutes");

            $notification = $this->em->createQueryBuilder()
                ->select('n', 'c')
                ->from('App:User\Addons\Notification', 'n')
                ->leftJoin('n.causers', 'c')
                ->where('n.userId = :targetId')
                ->andWhere('n.type = :type')
                ->andWhere('n.timestamp > :since')
                ->setParameter('targetId', $targetUser)
                ->setParameter('type', $type)
                ->setParameter('since', $since)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if($notification INSTANCEOF Notification){
                $notification->addCauser($causer);
                $notification->setSeen(false);
                $notification->setTimestamp(new \DateTime());

                $this->em->flush();

                return;
            }
        }

        if(
            !is_null($post) &&
            !in_array($type, [Notification::$TYPE_MENTION, Notification::$TYPE_REPLY_MENTION])
        ){
            $notification = $this->em->getRepository(Notification::class)->findOneBy([
                'postId' => $post,
                'type' => $type,
            ]);

            if($notification INSTANCEOF Notification){
                $notification->addCauser($causer);
                $notification->setSeen(false);
                $notification->setTimestamp(new \DateTime());

                $this->em->flush();

                return;
            }
        }

        $notification = new Notification();
        $notification->setUserId($targetUser);
        $notification->setPostId($post);
        $notification->setType($type);
        $notification->addCauser($causer);
        $notification->setMessage($message);

        $this->em->persist($notification);
        $this->em->flush();
    }

    public function removeCauser(string $notificationId, User $causer): void
    {
        $notification = $this->em->getRepository(Notification::class)->findOneBy([
            'id' => $notificationId,
        ]);

        if(!$notification INSTANCEOF Notification){
            return;
        }

        $notification->removeCauser($causer);

        if($notification->getInteractionsCount() <= 1){
            $this->em->remove($notification);
        }

        $this->em->flush();
    }

    public function fetch(string $userId): array
    {
        $qb = $this->em->createQueryBuilder();

        $ids = $qb
            ->select('n.id')
            ->from('App:User\Addons\Notification', 'n')
            ->where('n.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('n.timestamp', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getArrayResult();
        $ids = array_column($ids, 'id');

        $qb = $this->em->createQueryBuilder();

        $notifications = $qb
            ->select('n', 'c')
            ->from('App:User\Addons\Notification', 'n')
            ->leftJoin('n.causers', 'c')
            ->where('n.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('n.timestamp', 'DESC')
            ->getQuery()
            ->getArrayResult();

        $qb = $this->em->createQueryBuilder();

        $qb->update('App:User\Addons\Notification', 'n')
            ->where('n.userId = :userId AND n.seen = false')
            ->set('n.seen', $qb->expr()->literal(true))
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();

        if(empty($notifications)){
            return [];
        }

        $userIds = array_column(
            call_user_func_array(
                'array_merge', array_column($notifications, 'causers')
            ),
            'id'
        );

        $users = $this->em->getRepository(User::class)
            ->findSanitizedUsers($userIds, $this->follow->get($userId), $this->block->get($userId));


        $postIds = array_column(
            $notifications,
            'postId'
        );

        $posts = $this->multiple->multiple($postIds, 50);

        $array = [];
        foreach($notifications as $notification){
            $sanitized = new SanitizedNotification($notification);

            foreach($notification['causers'] as $causer){
               foreach($users as $user){
                   if($user->getId() === $causer['id']){
                       $sanitized->addCauser($user);
                   }
               }
            }

            foreach($posts as $post){
                if($notification['postId'] === $post['id']){
                    $sanitized->setPost($post);
                }
            }

            $array[] = $sanitized->toArray();
        }

        return $array;
    }

    /**
     * @param string $userId
     * @return array|int[]
     */
    public function count(string $userId): array
    {
        $qb = $this->em->createQueryBuilder();

        $count = $qb->select('COUNT(n) as count')
            ->from('App:User\Addons\Notification', 'n')
            ->where('n.userId = :userId AND n.seen = false')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();

        $notificationCount = 0;
        if(isset($count['count'])){
            $notificationCount = intval($count['count']);
        }

        $qb = $this->em->createQueryBuilder();

        $count = $qb->select('COUNT(f) as count')
            ->from('App:User\Addons\FollowRequest', 'f')
            ->where('f.requestedId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();

        $requestCount = 0;
        if(isset($count['count'])){
            $requestCount = intval($count['count']);
        }

        return [
            'notifications' => $notificationCount,
            'requests' => $requestCount,
        ];
    }

    /**
     * @param string $userId
     * @param string $notificationId
     * @return array
     */
    public function fetchCausers(string $userId, string $notificationId): array
    {
        $qb = $this->em->createQueryBuilder();

        $notifications = $qb->select('n', 'c')
            ->from('App:User\Addons\Notification', 'n')
            ->where('n.userId = :userId')
            ->andWhere('n.id = :notificationId')
            ->leftJoin('n.causers', 'c')
            ->setParameter('userId', $userId)
            ->setParameter('notificationId', $notificationId)
            //->orderBy('n.id', 'DESC')
            ->orderBy('n.timestamp', 'DESC')
            ->setMaxResults(40)
            ->getQuery()
            ->getArrayResult();

        if(empty($notifications)){
            return [];
        }

        $causers = $notifications[0]['causers'];
        $ids = array_column($causers, "id");

        $users = $this->em->getRepository(User::class)->findSanitizedUsers($ids);

        return array_map(function($user) {
            return $user->toArray();
        }, $users);
    }
}
