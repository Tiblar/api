<?php

namespace App\Controller\Actions\User\Actions;

use App\Controller\ApiController;
use App\Entity\User\Addons\Follow;
use App\Entity\User\Addons\Notification;
use App\Entity\User\User;
use App\Service\User\Block;
use App\Service\User\Notifier;
use App\Service\User\Privacy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FollowController extends ApiController
{
    /**
     * @Route("/users/follow/{followId}", name="follow_user", methods={"POST"})
     */
    public function follow(Request $request, Notifier $notifier, Block $blockService, Privacy $privacyService, $followId)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'token' => 'Invalid token.'
            ], 'Authentication error.', 401);
        }

        $followUser = $em->getRepository(User::class)->findOneBy([
            'id' => $followId
        ]);

        if(!$followUser INSTANCEOF User || $followId === $this->getUser()->getId()){
            return $this->respondWithErrors([
                'id' => 'This user does not exist.'
            ], null, 404);
        }

        $follow = $em->getRepository(Follow::class)->findOneBy([
            'followedId' => $followId,
            'followerId' => $this->getUser()->getId()
        ]);

        if($follow INSTANCEOF Follow){
            return $this->respond([
                'follow' => $follow->toArray(),
            ]);
        }

        $privacy = $privacyService->get($followUser->getId());

        $view = $privacy->getView();

        if($view === \App\Entity\User\Addons\Privacy::$VIEW_FOLLOWERS){
            return $this->respondWithErrors([], "You must request to follow this user.", 403);
        }

        $block = $blockService->get($followUser->getId());

        if(in_array($this->getUser()->getId(), $block->getBlocking())){
            return $this->respondWithErrors([], "You have been blocked by this user.", 403);
        }

        $followUser->getInfo()->setFollowerCount($followUser->getInfo()->getFollowerCount() + 1);

        $follow = new Follow();
        $follow->setFollowerId($this->getUser()->getId());
        $follow->setFollowedId($followId);

        $em->persist($follow);
        $em->flush();

        $notifier->add($user, $followUser->getId(), Notification::$TYPE_FOLLOW, null);

        return $this->respond([
            'follow' => $follow->toArray(),
        ]);
    }

    /**
     * @Route("/users/follow/{followId}", name="unfollow_user", methods={"DELETE"})
     */
    public function unfollow(Request $request, Notifier $notifier, $followId)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'token' => 'Invalid token.'
            ], 'Authentication error.', 401);
        }

        $follow = $em->getRepository(Follow::class)->findOneBy([
            'followedId' => $followId,
            'followerId' => $this->getUser()->getId()
        ]);

        if(!$follow INSTANCEOF Follow){
            return $this->respondWithErrors([], null, 404);
        }

        $followUser = $em->getRepository(User::class)->findOneBy([
            'id' => $followId
        ]);

        if(!$followUser INSTANCEOF User || $followId === $this->getUser()->getId()){
            return $this->respondWithErrors([
                'id' => 'This user does not exist.'
            ], null, 404);
        }

        $followUser->getInfo()->setFollowerCount($followUser->getInfo()->getFollowerCount() - 1);

        $em->remove($follow);
        $em->flush();

        $since = new \DateTime();
        $since->modify("-10 minutes");

        $notification = $em->createQueryBuilder()
            ->select('n', 'c')
            ->from('App:User\Addons\Notification', 'n')
            ->leftJoin('n.causers', 'c')
            ->where('n.userId = :targetId')
            ->andWhere('n.type = :type')
            ->andWhere('n.timestamp > :since')
            ->setParameter('targetId', $followUser->getId())
            ->setParameter('type', Notification::$TYPE_FOLLOW)
            ->setParameter('since', $since)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if($notification INSTANCEOF Notification){
            $notifier->removeCauser($notification->getId(), $user);
        }else{
            $notifier->add($user, $followUser->getId(), Notification::$TYPE_UNFOLLOW);
        }

        return $this->respond([]);
    }
}