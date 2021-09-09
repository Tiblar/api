<?php

namespace App\Controller\Actions\Lists;

use App\Controller\ApiController;
use App\Entity\Lists\ListItem;
use App\Entity\Lists\PostList;
use App\Service\Post\Retrieve\Fetch\Single;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;

class ManageListController extends ApiController
{
    /**
     * @Route("/list/{listId}/{postId}", name="add_post_list", methods={"POST"})
     */
    public function addPost(
        Request $request, Single $single, $listId, $postId
    ) {
        $em = $this->getDoctrine()->getManager();

        $list = $em->getRepository(PostList::class)->findOneBy([
            'id' => $listId,
            'author' => $this->getUser()->getId(),
        ]);

        if(!($list instanceof PostList)){
            return $this->respondWithErrors([
                'listId' => 'List not found'
            ], null, 404);
        }

        if($list->getAuthor()->getId() !== $this->getUser()->getId()){
            return $this->respondWithErrors([
                'listId' => 'List not found'
            ], null, 404);
        }

        $post = $single->single($postId);

        if(is_null($post) || empty($post)){
            return $this->respondWithErrors([
                'postId' => 'Post not found'
            ], null, 404);
        }

        $listItem = new ListItem();
        $listItem->setUserId($this->getUser()->getId());
        $listItem->setPostId($postId);
        $listItem->setListId($listId);
        $em->persist($listItem);
        $em->flush();

        return $this->respond([]);
    }

    /**
     * @Route("/list/{listId}/{postId}", name="delete_post_list", methods={"DELETE"})
     */
    public function removePost(
        Request $request, $listId, $postId
    ) {
        $em = $this->getDoctrine()->getManager();

        $list = $em->getRepository(PostList::class)->findOneBy([
            'id' => $listId,
            'author' => $this->getUser()->getId(),
        ]);

        if(!($list instanceof PostList)){
            return $this->respondWithErrors([
                'listId' => 'List not found'
            ], null, 404);
        }

        if($list->getAuthor()->getId() !== $this->getUser()->getId()){
            return $this->respondWithErrors([
                'listId' => 'List not found'
            ], null, 404);
        }

        $em->createQueryBuilder()
            ->delete('App:Lists\ListItem', 'l')
            ->where('l.listId = :listId')
            ->andWhere('l.userId = :userId')
            ->andWhere('l.postId = :postId')
            ->setParameter('listId', $listId)
            ->setParameter('userId', $this->getUser()->getId())
            ->setParameter('postId', $postId)
            ->getQuery()->getResult();

        return $this->respond([]);
    }
}