<?php

namespace App\Controller\Actions\Post\Actions;

use App\Controller\ApiController;
use App\Entity\Post\Favorite;
use App\Entity\Post\Post;
use App\Entity\User\Addons\Notification;
use App\Service\User\Block;
use App\Service\User\Notifier;
use App\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FavoriteController extends ApiController
{
    /**
     * @Route("/post/favorite/{postId}", name="favorite_post", methods={"POST"})
     */
    public function favorite(Request $request, Block $blockService, Notifier $notifier, $postId)
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

        if($post->isPrivate() && $post->getAuthor()->getId() !== $this->getUser()->getId()){
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

        $block = $blockService->get($post->getAuthor()->getId());

        if(in_array($this->getUser()->getId(), $block->getBlocking())){
            return $this->respondWithErrors([], "You have been blocked by this user.", 403);
        }

        $favorite = $em->getRepository(Favorite::class)->findOneBy([
            'postId' => $post->getId(),
            'favoriter' => $this->getUser()->getId()
        ]);

        if($favorite INSTANCEOF Favorite){
            return $this->respond([
                'favorite' => $favorite->toArray(),
            ]);
        }

        $favorite = new Favorite();
        $favorite->setFavorited($post->getAuthor()->getId());
        $favorite->setFavoriter($this->getUser()->getId());
        $favorite->setPost($post->getId());
        $em->persist($favorite);

        $post->setFavoritesCount($post->getFavoritesCount() + 1);

        $em->flush();

        $notifier->add($user, $post->getAuthor()->getId(), Notification::$TYPE_FAVORITE, $post->getId());

        return $this->respond([
            'favorite' => $favorite->toArray(),
        ]);
    }

    /**
     * @Route("/post/unfavorite/{postId}", name="unfavorite_post", methods={"POST"})
     */
    public function unfavorite(Request $request, Notifier $notifier, $postId)
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

        $favorite = $em->getRepository(Favorite::class)->findOneBy([
            'postId' => $post->getId(),
            'favoriter' => $this->getUser()->getId()
        ]);

        if(!$favorite INSTANCEOF Favorite){
            return $this->respond([]);
        }

        $em->remove($favorite);
        $post->setFavoritesCount($post->getFavoritesCount() - 1);

        if($post->getFavoritesCount() < 0){
            $post->setFavoritesCount(0);
        }

        $em->flush();

        $notification = $em->getRepository(Notification::class)->findOneBy([
            'postId' => $postId,
            'type' => Notification::$TYPE_FAVORITE,
        ]);

        if($notification INSTANCEOF Notification){
            $notifier->removeCauser($notification->getId(), $user);
        }

        return $this->respond([]);
    }
}