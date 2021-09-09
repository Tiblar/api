<?php
namespace App\Service\User;

use App\Entity\User\Addons\FollowRequest;
use App\Structure\User\SanitizedUser;
use App\Structure\User\FollowStructure;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;

class Follow
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Security
     */
    private $security;

    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    /**
     * @param $userId
     * @param int $offset
     * @param int $limit
     * @return FollowStructure
     */
    public function get($userId, $offset = null, $limit = null): FollowStructure
    {
        $qb = $this->em->createQueryBuilder()
            ->select('f.followedId, r.requestedId')
            ->from(\App\Entity\User\Addons\Follow::class, 'f')
            ->leftJoin(FollowRequest::class, 'r', 'WITH', 'r.requesterId = :userId')
            ->where('(f.followerId = :userId)')
            ->setParameter('userId', $userId);

        if(!is_null($offset)){
            $qb->setFirstResult($offset);
        }

        if(!is_null($offset)){
            $qb->setMaxResults($limit);
        }

        $followed = $qb->getQuery()
            ->getResult();

        $qb = $this->em->createQueryBuilder()
            ->select('f.followerId')
            ->from(\App\Entity\User\Addons\Follow::class, 'f')
            ->where('(f.followedId = :userId)')
            ->setParameter('userId', $userId);

        if(!is_null($offset)){
            $qb->setFirstResult($offset);
        }

        if(!is_null($offset)){
            $qb->setMaxResults($limit);
        }

        $followers = $qb->getQuery()
            ->getResult();

        return new FollowStructure(
            array_column($followed, 'followedId'),
            array_column($followers, 'followerId'),
            array_column($followed, 'requestId')
        );
    }

    public function relationship($userId)
    {
        if(!$this->security->isGranted("ROLE_USER") || $userId === $this->security->getUser()->getId()){
            return [
                'is_follower' => false,
                'is_followed' => false,
            ];
        }

        $follow = $this->get($userId);
        $id = $this->security->getToken()->getUser()->getId();

        $array = [
            'is_follower' => false,
            'is_followed' => false,
        ];

        if(in_array($id, $follow->getFollowers())){
            $array['is_followed'] = true;
        }

        if(in_array($id, $follow->getFollowing())){
            $array['is_follower'] = true;
        }

        return $array;
    }
}
