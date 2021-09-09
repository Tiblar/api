<?php

namespace App\Controller\Actions\Post;

use App\Controller\ApiController;
use App\Entity\Post\Post;
use App\Entity\Post\Reply;
use App\Entity\User\User;
use App\Service\Post\Retrieve\Fetch\PostReplies;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GetInteractionsController extends ApiController
{
    /**
     * @Route("/post/interactions/{postId}", name="interactions_post_causers", methods={"GET"})
     */
    public function favoriteCausers(Request $request, $postId)
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
            ->select('f.favoriter')
            ->from('App:Post\Favorite', 'f')
            ->andWhere('f.postId = :postId')
            ->setParameter('postId', $postId)
            ->getQuery()
            ->getArrayResult();
        $userIds = array_column($userIds, "favoriter");

        $users = $em->getRepository(User::class)
            ->findSanitizedUsers($userIds);

        $favoriteUsers = array_map(function ($user) {
            return $user->toArray();
        }, $users);

        $userIds = $em->createQueryBuilder()
            ->select('p, a')
            ->from('App:Post\Post', 'p')
            ->leftJoin('p.author', 'a')
            ->where('p.reblog = :postId')
            ->setParameter('postId', $postId)
            ->getQuery()
            ->getArrayResult();
        $userIds = array_column(array_column($userIds, "author"), "id");

        $users = $em->getRepository(User::class)
            ->findSanitizedUsers($userIds);

        $reblogUsers = array_map(function ($user) {
            return $user->toArray();
        }, $users);

        return $this->respond([
            'favorite' => $favoriteUsers,
            'reblog' => $reblogUsers,
        ]);
    }
}