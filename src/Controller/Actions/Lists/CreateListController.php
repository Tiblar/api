<?php

namespace App\Controller\Actions\Lists;

use App\Controller\ApiController;
use App\Entity\Lists\PostList;
use App\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;

class CreateListController extends ApiController
{
    /**
     * @Route("/list", name="list_create", methods={"POST"})
     */
    public function createList(
        Request $request
    ) {
        $title = $request->request->get('title');
        $description = $request->request->get('description');
        $private = filter_var($request->request->get('private'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if(is_null($title) || empty($title) || strlen($title) === 0){
            return $this->respondWithErrors([
                'title' => 'Title cannot be empty.'
            ], null, 400);
        }

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'token' => 'Invalid token.'
            ], 'Authentication error.', 401);
        }

        $lists = $em->getRepository(PostList::class)->findBy([
            'author' => $this->getUser()->getId()
        ]);

        if(count($lists) >= 15){
            return $this->respondWithErrors([
                'lists' => 'You have hit the maximum amount (15) of lists.'
            ], null, 400);
        }

        $list = new PostList();
        $list->setAuthor($user);
        $list->setTitle(substr($title, 0, 50));
        if(!is_null($description) && is_string($description)){
            $list->setDescription(substr($description, 0, 800));
        }

        if(!is_null($private) && is_bool($private)){
            $visibility = $private ? PostList::$VISIBILITY_PRIVATE : PostList::$VISIBILITY_PUBLIC;
            $list->setVisibility($visibility);
        }

        $em->persist($list);
        $em->flush();
        $em->clear();

        return $this->respond([
            'list' => $list->toArray(),
        ]);
    }
}