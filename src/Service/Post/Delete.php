<?php
namespace App\Service\Post;

use App\Entity\Post\Post;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;

class Delete {

    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $postId
     * @return bool
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function delete(string $postId) {

        $post = $this->em->getRepository(Post::class)->findOneBy([
            'id' => $postId
        ]);

        if(!$post INSTANCEOF Post){
            return false;
        }

        $user = $this->em->getRepository(User::class)->findOneBy([
            'id' => $post->getAuthor()->getId(),
        ]);

        if(!$user INSTANCEOF User){
            return false;
        }

        $this->em->getConnection()->beginTransaction();

        try {
            $size = $this->em->createQueryBuilder()
                ->select('SUM(f.fileSize) as size')
                ->from('App:Media\Attachment', 'a')
                ->leftJoin('a.file', 'f')
                ->where('a.post = :postId')
                ->setParameter('postId', $post->getId())
                ->setMaxResults(1)
                ->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY)['size'];

            if(is_null($size)){
                $size = 0;
            }

            if($size > 0){
                $user->setStorage($user->getStorage() - $size);
            }

            $this->em->createQueryBuilder()
                ->delete('App:Post\Post', 'p')
                ->where('p.reblog = :postId')
                ->setParameter('postId', $post->getId())
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:User\Addons\Notification', 'n')
                ->where('n.postId = :postId')
                ->setParameter('postId', $post->getId())
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Post\Favorite', 'f')
                ->where('f.postId = :postId')
                ->setParameter('postId', $post->getId())
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Lists\ListItem', 'l')
                ->where('l.postId = :postId')
                ->setParameter('postId', $post->getId())
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Post\Mention', 'm')
                ->where('m.postId = :postId')
                ->setParameter('postId', $post->getId())
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Media\PollVote', 'v')
                ->where('v.postId = :postId')
                ->setParameter('postId', $post->getId())
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Post\Reply', 'r')
                ->where('r.post = :postId AND r.depth > 0')
                ->setParameter('postId', $post->getId())
                ->getQuery()->getResult();

            $this->em->createQueryBuilder()
                ->delete('App:Post\Reply', 'r')
                ->where('r.post = :postId')
                ->setParameter('postId', $post->getId())
                ->getQuery()->getResult();

            if($post->getReblog() INSTANCEOF Post){
                $original = $this->em->getRepository(Post::class)->findOneBy([
                    'id' => $post->getReblog()->getId(),
                ]);

                if($original INSTANCEOF Post){
                    $original->setReblogsCount($original->getReblogsCount() - 1);
                }

                if($original->getReblogsCount() < 0){
                    $original->setReblogsCount(0);
                }
            }

            $this->em->remove($post);
            $this->em->flush();

            $this->em->getConnection()->commit();

            return true;
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
        }

        return false;
    }
}