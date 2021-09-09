<?php

namespace App\Controller\Actions\Post;

use App\Controller\ApiController;
use App\Entity\User\Addons\Privacy as PrivacyEntity;
use App\Entity\User\User;
use App\Entity\Video\VideoHistory;
use App\Service\Post\Retrieve\Fetch\Single;
use App\Service\Search\Video;
use App\Service\User\Block;
use App\Service\User\Follow;
use App\Service\User\Privacy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;

class GetPostController extends ApiController
{
    /**
     * @Route("/post/{postId}", name="post", methods={"GET"})
     */
    public function newest(
        Request $request, Single $fetch, Security $security, Follow $followService,
        Block $blockService, Privacy $privacyService, $postId
    ) {
        $em = $this->getDoctrine()->getManager();

        $post = $fetch->single($postId);

        if(empty($post)){
            return $this->respondWithErrors([
                'id' => 'Post not found.'
            ], null, 404);
        }

        $userId = $security->isGranted("ROLE_USER") ? $this->getUser()->getId() : null;

        $lastId = $em->createQueryBuilder()
            ->select('h.postId')
            ->from('App:Video\VideoHistory', 'h')
            ->where('h.timestamp > :timestamp')
            ->andWhere('h.postId = :postId')
            ->setParameter('timestamp', new \DateTime("-10 minutes"))
            ->setParameter('postId', $postId)
            ->orderBy('h.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if(
            !is_null($userId) &&
            !is_null($post[0]['title']) &&
            !empty($post[0]['video_categories']) &&
            count($post[0]['attachments']) > 0 &&
            is_null($lastId)
        ){
            $privacy = $em->getRepository(PrivacyEntity::class)->findOneBy([
                'userId' => $userId,
            ]);

            if($privacy instanceof PrivacyEntity && $privacy->getVideoHistory()){
                $lastId = $em->createQueryBuilder()
                    ->select('h.postId')
                    ->from('App:Video\VideoHistory', 'h')
                    ->where('h.timestamp > :timestamp')
                    ->andWhere('h.postId != :postId')
                    ->setParameter('timestamp', new \DateTime("-60 minutes"))
                    ->setParameter('postId', $postId)
                    ->orderBy('h.id', 'DESC')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();

                $history = new VideoHistory();
                $history->setUserId($userId);
                $history->setPostId($postId);

                if(!is_null($lastId) && isset($lastId['postId'])){
                    $history->setLastId($lastId['postId']);
                }

                $em->persist($history);
                $em->flush();
            }
        }

        $user = $em->getRepository(User::class)
            ->findSanitizedUser($post[0]['author']['id']);

        $follow = $followService->get($userId);
        $block = $blockService->get($userId);

        $privacy = $privacyService->get($user->getId());

        if(in_array($user->getId(), $block->getBlockers())){
            return $this->respondWithErrors([
                'auth' => 'You have been blocked by this user.'
            ], "You have been blocked by this user.", 403);
        }

        if(
            $privacy->getView() === PrivacyEntity::$VIEW_FORMERLY_CHUCKS
            && null === $userId
        ){
            return $this->respondWithErrors([
                'auth' => 'You must login to see this user.'
            ], "You must login to see this user.", 403);
        }

        if(
            $privacy->getView() === PrivacyEntity::$VIEW_FOLLOWERS
            && $privacy->getUserId() !== $userId
            && !in_array($user->getId(), $follow->getFollowing())
        ){
            return $this->respondWithErrors([
                'auth' => 'You must follow to see this user.'
            ], "You must follow to see this user.", 403);
        }


        return $this->respond([
            'posts' => $post
        ]);
    }
}