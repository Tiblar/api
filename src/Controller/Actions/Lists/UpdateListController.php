<?php

namespace App\Controller\Actions\Lists;

use App\Controller\ApiController;
use App\Entity\Lists\PostList;
use App\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;

class UpdateListController extends ApiController
{
    /**
     * @Route("/list/{listId}", name="list_update", methods={"PATCH"})
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

        $title = $request->request->get('title');
        $description = $request->request->get('description');
        $private = filter_var($request->request->get('private'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if(is_null($title) || empty($title)){
            return $this->respondWithErrors([
                'title' => 'Title cannot be empty.'
            ], null, 400);
        }

        $list->setTitle(substr($title, 0, 50));
        if(!is_null($description) && is_string($description)){
            $list->setDescription(substr($description, 0, 400));
        }

        if(!is_null($private) && is_bool($private)){
            $visibility = $private ? PostList::$VISIBILITY_PRIVATE : PostList::$VISIBILITY_PUBLIC;
            $list->setVisibility($visibility);
        }

        $em->flush();

        return $this->respond([]);
    }
}