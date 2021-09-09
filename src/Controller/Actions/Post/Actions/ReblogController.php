<?php

namespace App\Controller\Actions\Post\Actions;

use App\Controller\ApiController;
use App\Entity\Media\File;
use App\Entity\Post\Favorite;
use App\Entity\Post\Post;
use App\Entity\User\Addons\Follow;
use App\Entity\User\Addons\Notification;
use App\Service\User\Block;
use App\Service\User\Notifier;
use App\Structure\User\SanitizedUser;
use App\Entity\User\User;
use App\Service\Content\Resource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ReblogController extends ApiController
{
    /**
     * @Route("/post/reblog/{postId}", name="reblog_post", methods={"POST"})
     */
    public function reblog(Request $request, Block $blockService, Notifier $notifier, $postId)
    {
        $em = $this->getDoctrine()->getManager();

        $post = $em->getRepository(Post::class)->findOneBy([
            'id' => $postId
        ]);

        $block = $blockService->get($post->getAuthor()->getId());

        if(in_array($this->getUser()->getId(), $block->getBlocking())){
            return $this->respondWithErrors([], "You have been blocked by this user.", 403);
        }

        if(!$post INSTANCEOF Post){
            return $this->respondWithErrors([
                'id' => 'This post does not exist.'
            ], null, 404);
        }


        if($post->isPrivate() && $post->getAuthor()->getId() !== $this->getUser()->getId()){
            return $this->respondWithErrors([
                'id' => 'This post does not exist.'
            ], null, 404);
        }

        $reblog = $em->createQueryBuilder()
            ->select('p')
            ->from('App:Post\Post', 'p')
            ->where('p.reblog = :postId AND p.author = :userId AND p.attachments IS EMPTY AND p.body IS NULL')
            ->setParameter('userId', $this->getUser()->getId())
            ->setParameter('postId', $postId)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        $check = $em->createQueryBuilder()
            ->select('p')
            ->from('App:Post\Post', 'p')
            ->where('p.id = :postId AND p.author = :userId AND p.attachments IS EMPTY AND p.body IS NULL')
            ->setParameter('userId', $this->getUser()->getId())
            ->setParameter('postId', $postId)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();

        if($reblog INSTANCEOF Post || ($check INSTANCEOF Post && $check->getReblog() INSTANCEOF Post)){
            return $this->respondWithErrors([], null, 404);
        }

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'token' => 'Invalid token.'
            ], 'Authentication error.', 401);
        }

        $reblog = new Post();
        $reblog->setAuthor($user);
        $reblog->setReblog($post);
        $reblog->setNsfw(false);

        $em->persist($reblog);

        $post->setReblogsCount($post->getReblogsCount() + 1);

        $em->flush();

        $notifier->add($user, $post->getAuthor()->getId(), Notification::$TYPE_REBLOG, $post->getId());

        return $this->respond([
            'reblog' => $reblog->toArray(),
        ]);
    }

    /**
     * @Route("/post/reblog/{postId}/list", name="reblog_post_causers", methods={"GET"})
     */
    public function reblogCausers(Request $request, Block $blockService, Notifier $notifier, $postId)
    {
        $em = $this->getDoctrine()->getManager();

        $post = $em->getRepository(Post::class)->findOneBy([
            'id' => $postId
        ]);

        if(!$post INSTANCEOF Post){
            return $this->respondWithErrors([
                'id' => 'This post does not exist.'
            ], null, 404);
        }

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'token' => 'Invalid token.'
            ], 'Authentication error.', 401);
        }

        if($post->getAuthor()->getId() !== $user->getId()){
            return $this->respondWithErrors([
                'id' => 'You do not have access to this post.'
            ], null, 403);
        }

        $userIds = $em->createQueryBuilder()
            ->select('p.id as post', 'a.id as author')
            ->from('App:Post\Post', 'p')
            ->leftJoin('p.author', 'a')
            ->andWhere('p.reblog = :postId')
            ->setParameter('postId', $postId)
            ->getQuery()
            ->getArrayResult();
        $userIds = array_unique(array_column($userIds, "author"));

        $users = $em->getRepository(User::class)
            ->findSanitizedUsers($userIds);

        $users = array_map(function ($user) {
            return $user->toArray();
        }, $users);

        return $this->respond([
            'users' => $users,
        ]);
    }

    /**
     * @Route("/post/unreblog/{postId}", name="unreblog_post", methods={"POST"})
     */
    public function unreblog(Request $request, Notifier $notifier, $postId)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->findOneBy([
            'id' => $postId,
        ]);

        if(!$post INSTANCEOF Post){
            return $this->respondWithErrors([
                'id' => 'This post does not exist.'
            ], null, 404);
        }

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'token' => 'Invalid token.'
            ], 'Authentication error.', 401);
        }

        $reblogs = $em->createQueryBuilder()
            ->select('p')
            ->from('App:Post\Post', 'p')
            ->where('(p.reblog = :postId AND p.author = :userId AND p.attachments IS EMPTY AND p.body IS NULL)')
            ->setParameter('postId', $postId)
            ->setParameter('userId', $this->getUser()->getId())
            ->getQuery()
            ->getResult();

        foreach($reblogs as $reblog){
            if(!$reblog INSTANCEOF Post){
                continue;
            }

            $em->remove($reblog);
        }

        $post->setReblogsCount($post->getReblogsCount() - 1);
        if($post->getReblogsCount() < 0){
            $post->setReblogsCount(0);
        }

        $em->flush();

        $notification = $em->getRepository(Notification::class)->findOneBy([
            'postId' => $postId,
            'type' => Notification::$TYPE_REBLOG,
        ]);

        if($notification INSTANCEOF Notification){
            $notifier->removeCauser($notification->getId(), $user);
        }

        return $this->respond([]);
    }
}