<?php

namespace App\Controller\Actions\Lists;

use App\Controller\ApiController;
use App\Entity\Lists\PostList;
use App\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;

class DeleteListController extends ApiController
{
    /**
     * @Route("/list/{listId}", name="list_delete", methods={"DELETE"})
     */
    public function deleteList(
        Request $request, $listId
    ) {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'token' => 'Invalid token.'
            ], 'Authentication error.', 401);
        }

        $list = $em->getRepository(PostList::class)->findOneBy([
            'id' => $listId,
            'author' => $user->getId(),
        ]);

        if(!($list instanceof PostList)){
            return $this->respondWithErrors([
                'id' => 'List not found'
            ], null, 404);
        }

        if($list->getAuthor()->getId() !== $user->getId()){
            return $this->respondWithErrors([
                'id' => 'List not found'
            ], null, 404);
        }

        $em->getConnection()->beginTransaction();
        try {
            $qb = $em->createQueryBuilder();
            $qb->delete('App:Lists\ListItem', 'l')
                ->where('l.listId = :listId')
                ->setParameter('listId', $list->getId())
                ->getQuery()
                ->execute();

            $qb = $em->createQueryBuilder();
            $qb->delete('App:Lists\PostList', 'l')
                ->where('l.id = :listId')
                ->setParameter('listId', $list->getId())
                ->getQuery()
                ->execute();

            $em->getConnection()->commit();

        } catch (\Exception $e) {
            $em->getConnection()->rollBack();
            return $this->respondWithErrors([], "Error deleting list.", 500);
        }

        return $this->respond([]);
    }
}