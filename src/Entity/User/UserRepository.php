<?php
declare(strict_types=1);

namespace App\Entity\User;

use App\Structure\User\BlockStructure;
use App\Structure\User\FollowStructure;
use App\Structure\User\SanitizedUser;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class UserRepository extends EntityRepository
{
    /**
     * @param string $usernameOrId
     * @return User|null
     * @throws NonUniqueResultException
     */
    public function findUser(string $usernameOrId): ?User
    {
        $user = $this->_em->createQueryBuilder()
            ->select('u, i, p')
            ->from('App:User\User', 'u')
            ->leftJoin('u.info', 'i')
            ->leftJoin('u.privacy', 'p')
            ->where('u.id = :usernameOrId OR i.username = :usernameOrId')
            ->setParameter('usernameOrId', $usernameOrId)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        if(!$user INSTANCEOF User){
            return null;
        }

        return $user;
    }

    /**
     * @param array $idsOrUsernames
     * @return array
     */
    public function findUsers(
        array $idsOrUsernames
    ): array
    {
        return $this->_em->createQueryBuilder()
            ->select('u, i, p')
            ->from('App:User\User', 'u')
            ->leftJoin('u.info', 'i')
            ->leftJoin('u.privacy', 'p')
            ->where('u.id in (:authors) OR i.username in (:authors)')
            ->setParameter('authors', $idsOrUsernames)
            ->getQuery()->getResult();
    }

    /**
     * @param string $usernameOrId
     * @param FollowStructure|null $followStructure
     * @param BlockStructure|null $blockStructure
     * @return SanitizedUser|null
     * @throws NonUniqueResultException
     */
    public function findSanitizedUser(
        string $usernameOrId, ?FollowStructure $followStructure = null, ?BlockStructure $blockStructure = null
    ): ?SanitizedUser
    {
        $user = $this->_em->createQueryBuilder()
            ->select('u, i, p')
            ->from('App:User\User', 'u')
            ->leftJoin('u.info', 'i')
            ->leftJoin('u.privacy', 'p')
            ->where('u.id = :usernameOrId OR i.username = :usernameOrId')
            ->setParameter('usernameOrId', $usernameOrId)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        if(!$user INSTANCEOF User){
            return null;
        }

        $sanitized = new SanitizedUser($user);

        $this->actions($sanitized, $followStructure, $blockStructure);

        return $sanitized;
    }

    /**
     * @param array $idsOrUsernames
     * @param FollowStructure|null $followStructure
     * @param BlockStructure|null $blockStructure
     * @return array
     */
    public function findSanitizedUsers(
        $idsOrUsernames, ?FollowStructure $followStructure = null, ?BlockStructure $blockStructure = null
    ): array
    {
        $users = $this->_em->createQueryBuilder()
            ->select('u, i, p')
            ->from('App:User\User', 'u')
            ->leftJoin('u.info', 'i')
            ->leftJoin('u.privacy', 'p')
            ->where('u.id in (:authors) OR i.username IN (:authors)')
            ->setParameter('authors', $idsOrUsernames)
            ->getQuery()->getResult();

        $arr = [];
        foreach($users as $user){
            $sanitized = new SanitizedUser($user);

            $this->actions($sanitized, $followStructure, $blockStructure);

            $arr[] = $sanitized;
        }

        return $arr;
    }

    private function actions(&$sanitized, $followStructure, $blockStructure)
    {
        if($followStructure INSTANCEOF FollowStructure){
            if(in_array($sanitized->getId(), $followStructure->getFollowing())){
                $sanitized->getInfo()->setFollowing(true);
            }else{
                $sanitized->getInfo()->setFollowing(false);
            }

            if(in_array($sanitized->getId(), $followStructure->getFollowers())){
                $sanitized->getInfo()->setFollowedBy(true);
            }else{
                $sanitized->getInfo()->setFollowedBy(false);
            }
        }

        if($blockStructure INSTANCEOF BlockStructure){
            if(in_array($sanitized->getId(), $blockStructure->getBlocking())){
                $sanitized->getInfo()->setBlocking(true);
            }else{
                $sanitized->getInfo()->setBlocking(false);
            }

            if(in_array($sanitized->getId(), $blockStructure->getBlockers())){
                $sanitized->getInfo()->setBlockedBy(true);
            }else{
                $sanitized->getInfo()->setBlockedBy(false);
            }
        }
    }
}
