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

class VisibilityController extends ApiController
{
    /**
     * @Route("/post/visibility/{postId}/everyone", name="visibility_everyone_post", methods={"PATCH"})
     */
    public function everyone(Request $request, $postId)
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

        if($post->getAuthor()->getId() !== $this->getUser()->getId()){
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

        $post->setFollowersOnly(false);
        $post->setPrivate(false);

        $em->flush();
        $em->clear();

        return $this->respond([]);
    }

    /**
     * @Route("/post/visibility/{postId}/private", name="visibility_private_post", methods={"PATCH"})
     */
    public function private(Request $request, $postId)
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

        if($post->getAuthor()->getId() !== $this->getUser()->getId()){
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

        $post->setFollowersOnly(false);
        $post->setPrivate(true);

        $em->flush();
        $em->clear();

        return $this->respond([]);
    }

    /**
     * @Route("/post/visibility/{postId}/followers", name="visibility_followers_post", methods={"PATCH"})
     */
    public function followers(Request $request, $postId)
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

        if($post->getAuthor()->getId() !== $this->getUser()->getId()){
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

        $post->setFollowersOnly(true);
        $post->setPrivate(false);

        $em->flush();
        $em->clear();

        return $this->respond([]);
    }
}