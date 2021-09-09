<?php
namespace App\Service;

use App\Entity\User\User;
use App\Structure\User\SanitizedUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class Leaderboard
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

    public function posts()
    {
        $total = 0;
        if($this->security->isGranted("ROLE_USER")){
            $total = $this->em->createQuery('SELECT COUNT(p) as count FROM App:Post\Post p
                                    WHERE p.author = :id')
                ->setParameter('id', $this->security->getToken()->getUser()->getId())
                ->getArrayResult();
            $total = array_column($total, 'count')[0];
        }

        $nsfw = $this->em->createQuery('SELECT i.id as id from App:User\UserInfo i
                                            WHERE i.nsfw = true')
            ->getArrayResult();
        $nsfw = array_column($nsfw, 'id');

        if(empty($nsfw)){
            $nsfw = [1, 2];
        }

        $stats = $this->em->createQueryBuilder()
            ->select('COUNT(p) as count, a.id as author')
            ->from('App:Post\Post', 'p')
            ->leftJoin('p.author', 'a')
            ->andWhere('p.id NOT IN (:nsfw)')
            ->setParameter('nsfw', $nsfw)
            ->groupBy('a.id')
            ->orderBy('count', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult();

        $users = $this->em->getRepository(User::class)
            ->findSanitizedUsers(array_column($stats, 'author'));

        foreach($stats as $i => &$stat){
            foreach($users as $user){
                if($stat['author'] === $user->getId()){
                    $stat['author'] = $user;
                    continue;
                }
            }

            if(!isset($stat['author']) || !$stat['author'] INSTANCEOF SanitizedUser){
                unset($stats[$i]);
            }else{
                $stat['author'] = $stat['author']->toArray();
            }
        }

        return [
            'my_total' => $total,
            'list' => $stats
        ];
    }

    public function invites()
    {
        $total = 0;
        if($this->security->isGranted("ROLE_USER")){
            $total = $this->em->createQuery('SELECT COUNT(i.id) as count FROM App:User\Addons\Invite i
                                    WHERE i.inviter = :id and i.complete = true')
                ->setParameter('id', $this->security->getToken()->getUser()->getId())
                ->getArrayResult();
            $total = array_column($total, 'count')[0];
        }

        $nsfw = $this->em->createQuery('SELECT i.id as id from App:User\UserInfo i
                                            WHERE i.nsfw = true')
            ->getArrayResult();
        $nsfw = array_column($nsfw, 'id');

        if(empty($nsfw)){
            $nsfw = [1, 2];
        }

        $stats = $this->em->createQuery('SELECT COUNT(i.id) as count, i.inviter as author FROM App:User\Addons\Invite i
                                   WHERE i.complete = true AND i.inviter NOT IN (:nsfw) GROUP BY i.inviter ORDER BY count DESC')
            ->setParameter('nsfw', $nsfw)
            ->setMaxResults(10)
            ->getArrayResult();

        $users = $this->em->getRepository(User::class)
            ->findSanitizedUsers(array_column($stats, 'author'));

        foreach($stats as $i => &$stat){
            foreach($users as $user){
                if($stat['author'] === $user->getId()){
                    $stat['author'] = $user;
                    continue;
                }
            }

            if(!isset($stat['author']) || !$stat['author'] INSTANCEOF SanitizedUser){
                unset($stats[$i]);
            }else{
                $stat['author'] = $stat['author']->toArray();
            }
        }

        return [
            'my_total' => $total,
            'list' => $stats
        ];
    }

    public function favorites()
    {
        $total = 0;
        if($this->security->isGranted("ROLE_USER")){
            $total = $this->em->createQuery('SELECT COUNT(f.id) as count FROM App:Post\Favorite f
                                    WHERE f.favorited = :id')
                ->setParameter('id', $this->security->getToken()->getUser()->getId())
                ->getArrayResult();
            $total = array_column($total, 'count')[0];
        }

        $nsfw = $this->em->createQuery('SELECT i.id as id from App:User\UserInfo i
                                            WHERE i.nsfw = true')
            ->getArrayResult();
        $nsfw = array_column($nsfw, 'id');

        if(empty($nsfw)){
            $nsfw = [1, 2];
        }

        $stats = $this->em->createQuery('SELECT COUNT(f.id) as count, f.favorited as author FROM App:Post\Favorite f
                                 WHERE f.favorited NOT IN (:nsfw) GROUP BY f.favorited ORDER BY count DESC')
            ->setParameter('nsfw', $nsfw)
            ->setMaxResults(10)
            ->getArrayResult();

        $users = $this->em->getRepository(User::class)
            ->findSanitizedUsers(array_column($stats, 'author'));

        foreach($stats as $i => &$stat){
            foreach($users as $user){
                if($stat['author'] === $user->getId()){
                    $stat['author'] = $user;
                    continue;
                }
            }

            if(!isset($stat['author']) || !$stat['author'] INSTANCEOF SanitizedUser){
                unset($stats[$i]);
            }else{
                $stat['author'] = $stat['author']->toArray();
            }
        }

        return [
            'my_total' => $total,
            'list' => $stats
        ];
    }

    public function followers()
    {
        $total = 0;
        if($this->security->isGranted("ROLE_USER")){
            $total = $this->em->createQuery('SELECT i.followerCount as count FROM App:User\UserInfo i
                                    WHERE i.id = :id')
                ->setParameter('id', $this->security->getToken()->getUser()->getId())
                ->getArrayResult();
            $total = array_column($total, 'count')[0];
        }

        $stats = $this->em->createQuery('SELECT i.followerCount as count, i.id as author FROM App:User\UserInfo i
                                    WHERE i.nsfw = false ORDER BY count DESC')
            ->setMaxResults(10)
            ->getArrayResult();

        $users = $this->em->getRepository(User::class)
            ->findSanitizedUsers(array_column($stats, 'author'));

        foreach($stats as $i => &$stat){
            foreach($users as $user){
                if($stat['author'] === $user->getId()){
                    $stat['author'] = $user;
                    continue;
                }
            }

            if(!isset($stat['author']) || !$stat['author'] INSTANCEOF SanitizedUser){
                unset($stats[$i]);
            }else{
                $stat['author'] = $stat['author']->toArray();
            }
        }

        return [
            'my_total' => $total,
            'list' => $stats
        ];
    }
}
