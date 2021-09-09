<?php

namespace App\Controller\Actions\Lists;

use App\Controller\ApiController;
use App\Entity\Lists\ListItem;
use App\Entity\Lists\PostList;
use App\Entity\User\User;
use App\Service\Post\Retrieve\Fetch\Multiple;
use App\Service\User\GetMe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;

class GetListController extends ApiController
{

    /**
     * @Route("/lists", name="get_lists", methods={"GET"})
     */
    public function getLists(
        Request $request, GetMe $me
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

        $lists = $em->createQueryBuilder()
            ->select('l, a')
            ->from('App:Lists\PostList', 'l')
            ->leftJoin('l.author', 'a')
            ->where('l.author = :userId')
            ->setParameter('userId', $user->getid())
            ->getQuery()
            ->getArrayResult();

        foreach($lists as &$list){
            $list['author'] = $me->toArray($user);
        }

        return $this->respond([
            'lists' => array_reverse($lists),
        ]);
    }

    /**
     * @Route("/list/{listId}", name="get_list", methods={"GET"})
     */
    public function getList(
        Request $request, Security $security, $listId
    ) {
        $em = $this->getDoctrine()->getManager();

        $list = $em->getRepository(PostList::class)->findOneBy([
            'id' => $listId,
        ]);

        if(!($list instanceof PostList)){
            return $this->respondWithErrors([
                'id' => 'List not found'
            ], null, 404);
        }

        if(
            $list->getVisibility() === PostList::$VISIBILITY_PRIVATE &&
            (!$security->isGranted("ROLE_USER") ||
            $list->getAuthor()->getId() !== $this->getUser()->getId())
        ){
            return $this->respondWithErrors([
                'id' => 'List not found'
            ], null, 404);
        }

        return $this->respond([
            'list' => $list->toArray(),
        ]);
    }

    /**
     * @Route("/list/{listId}/posts", name="get_list_posts", methods={"GET"})
     */
    public function getListPosts(
        Request $request, Security $security, Multiple $multiple, $listId
    ) {
        $em = $this->getDoctrine()->getManager();

        $limit = $request->query->get('limit');
        $offset = $request->query->get('offset');

        $list = $em->getRepository(PostList::class)->findOneBy([
            'id' => $listId,
        ]);

        if(!($list instanceof PostList)){
            return $this->respondWithErrors([
                'id' => 'List not found'
            ], null, 404);
        }

        if(
            $list->getVisibility() === PostList::$VISIBILITY_PRIVATE &&
            (!$security->isGranted("ROLE_USER") ||
                $list->getAuthor()->getId() !== $this->getUser()->getId())
        ){
            return $this->respondWithErrors([
                'id' => 'List not found'
            ], null, 404);
        }

        if(!is_null($offset)){
            $offset = intval($offset);
        }

        if($limit < 10 || $limit > 100){
            $limit = 10;
        }

        $qb = $em->createQueryBuilder();
        $qb->select('l.postId');
        $qb->from('App:Lists\ListItem', 'l');
        $qb->where('l.listId = :listId');
        $qb->setParameter('listId', $listId);
        $qb->setFirstResult($offset);
        $qb->setMaxResults($limit);

        $postIds = $qb->getQuery()
            ->getArrayResult();
        $postIds = array_column($postIds, "postId");

        $posts = $multiple->multiple($postIds, $limit);

        return $this->respond([
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/post/{postId}/lists", name="get_lists_post", methods={"GET"})
     */
    public function getListPostsLists(
        Request $request, Multiple $multiple, $postId
    ) {
        $em = $this->getDoctrine()->getManager();

        $items = $em->getRepository(ListItem::class)->findBy([
            'postId' => $postId,
            'userId' => $this->getUser()->getId(),
        ]);

        $ids = [];

        foreach($items as $item){
            $ids[] = $item->getListId();
        }

        return $this->respond([
            'lists' => $ids,
        ]);
    }
}