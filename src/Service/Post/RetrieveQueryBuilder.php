<?php
namespace App\Service\Post;

use App\Service\Post\Retrieve\AddonsBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class RetrieveQueryBuilder {

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var AddonsBuilder
     */
    private $addonsBuilder;

    public function __construct(EntityManagerInterface $em, AddonsBuilder $addonsBuilder)
    {
        $this->em = $em;
        $this->addonsBuilder = $addonsBuilder;
    }

   public function getQueryBuilder(bool $nsfw, ?int $limit, ?int $offset, bool $staff = false): QueryBuilder
   {
       $limit = intval($limit);
       $offset = intval($offset);

       if(($limit < 1) && !$staff){
           $limit = 10;
       }

       if(($limit > 100) && !$staff){
           $limit = 100;
       }

       $block = $this->addonsBuilder->getBlock();

       $blocked = empty($block->getBlocking()) ? [1, 2] : $block->getBlocking();
       $blockers = empty($block->getBlockers()) ? [1, 2] : $block->getBlockers();

       $qb = $this->em->createQueryBuilder();
       $qb->select('p.id');
       $qb->from('App:Post\Post', 'p');

       $qb->andWhere('(p.private != 1 OR (p.private = 1 AND p.author = :userId))');
       $qb->andWhere('p.author NOT IN (:blocked)');
       $qb->andWhere('(p.author NOT IN (:blockers))');
       $qb->andWhere('(p.markDelete = false)');
       $qb->andWhere('(p.hidden = false)');
       $qb->andWhere('(p.spam = false OR (p.spam = true AND p.author = :userId) OR (:staff = true AND p.spam = true))');
       $qb->andWhere('(p.followersOnly = false OR (p.followersOnly = true AND (p.author = :userId OR :userId IN 
           (' .
               $this->em
                   ->createQueryBuilder()
                   ->select('f.followerId')
                   ->from('App:User\Addons\Follow', 'f')
                   ->where('p.author = f.followedId')
                   ->getDQL()
           . ') OR :staff = true)))');

       $qb->having(
           $qb->expr()->gt(
               "(" .
               $this->em
                   ->createQueryBuilder()
                   ->select('COUNT(a.id)')
                   ->from('App:User\User', 'a')
                   ->where('a.banned = false AND a.id = p.author')
                   ->getDQL()
               . ")"
               ,
               0
           )
       );

       if($nsfw){
           $qb->andWhere('(p.nsfw = 1)');
       }

       $qb->having(
           $qb->expr()->gt(
               "(" .
               $this->em
                   ->createQueryBuilder()
                   ->select('COUNT(pr.id)')
                   ->from('App:User\Addons\Privacy', 'pr')
                   ->where('pr.userId = p.author')
                   ->andWhere('(
                        pr.view = 0 OR
                        (pr.view = 1 AND :userId IS NOT NULL) OR
                        (pr.view = 2 AND (:userId = p.author OR 1 = (' .
                           $this->em
                               ->createQueryBuilder()
                               ->select('COUNT(f2)')
                               ->from('App:User\Addons\Follow', 'f2')
                               ->where('f2.followedId = p.author')
                               ->andWhere('f2.followerId = :userId')
                               ->getDQL()
                        . ')))
                   )')
                   ->getDQL()
               . ")"
               ,
               0
           )
       );

       $qb->setParameter('userId', $this->addonsBuilder->getUserId())
           ->setParameter('staff', $staff)
           ->setParameter('blocked', $blocked)
           ->setParameter('blockers', $blockers);

       if(!$staff){
           $qb->setFirstResult($offset);
       }
       $qb->setMaxResults($limit);

       return $qb;
   }
}
