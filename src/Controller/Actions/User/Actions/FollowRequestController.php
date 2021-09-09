<?php

namespace App\Controller\Actions\User\Actions;

use App\Controller\ApiController;
use App\Entity\User\Addons\FollowRequest;
use App\Entity\User\Addons\Notification;
use App\Service\User\Block;
use App\Service\User\Follow;
use App\Entity\User\User;
use App\Service\User\Notifier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FollowRequestController extends ApiController
{
    /**
     * @Route("/users/follow/request/{usernameOrId}", name="follow_request_user", methods={"POST"})
     */
    public function request(Request $request, Block $blockService, Follow $followService, Notifier $notifier, $usernameOrId)
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

        $followUser = $em->getRepository(User::class)->findUser($usernameOrId);

        if(!$followUser INSTANCEOF User || $usernameOrId === $this->getUser()->getId()){
            return $this->respondWithErrors([
                'id' => 'This user does not exist.'
            ], null, 404);
        }

        $follow = $followService->get($this->getUser()->getId());

        if(in_array($followUser->getId(), $follow->getRequests())){
            return $this->respondWithErrors([
                'id' => 'You already have a request for this user.'
            ], null, 400);
        }

        $block = $blockService->get($followUser->getId());

        if(in_array($this->getUser()->getId(), $block->getBlocking())){
            return $this->respondWithErrors([], "You have been blocked by this user.", 403);
        }

        $followRequest = new FollowRequest();
        $followRequest->setRequestedId($followUser->getId());
        $followRequest->setRequesterId($this->getUser()->getId());

        $em->persist($followRequest);
        $em->flush();

        $notifier->add($user, $followUser->getId(), Notification::$TYPE_FOLLOW_REQUEST);

        return $this->respond([
            'request' => $followRequest->toArray(),
        ]);
    }

    /**
     * @Route("/users/follow/request/{usernameOrId}", name="follow_unrequest_user", methods={"DELETE"})
     */
    public function unrequest(Request $request, $usernameOrId)
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

        $followUser = $em->getRepository(User::class)->findUser($usernameOrId);

        if(!$followUser INSTANCEOF User){
            return $this->respondWithErrors([], null, 404);
        }

        $followRequest = $em->getRepository(FollowRequest::class)->findOneBy([
            'requestedId' => $followUser->getId(),
            'requesterId' => $this->getUser()->getId()
        ]);

        if($followRequest INSTANCEOF FollowRequest){
            $notifications = $em->createQueryBuilder()
                ->select('n', 'c')
                ->from('App:User\Addons\Notification', 'n')
                ->leftJoin('n.causers', 'c')
                ->where('n.userId = :targetId')
                ->andWhere('n.type = :type')
                ->andWhere('c.id = :causerId')
                ->setParameter('targetId', $followUser->getId())
                ->setParameter('type', Notification::$TYPE_FOLLOW_REQUEST)
                ->setParameter('causerId', $user->getId())
                ->setMaxResults(1)
                ->getQuery()
                ->getResult();

            foreach($notifications as $notification){
                $em->remove($notification);
            }

            $em->remove($followRequest);
            $em->flush();
        }else{
            return $this->respondWithErrors([], null, 404);
        }

        return $this->respond([]);
    }

    /**
     * @Route("/users/follow/request/{usernameOrId}/status", name="follow_request_status", methods={"GET"})
     */
    public function status(Request $request, $usernameOrId)
    {
        $em = $this->getDoctrine()->getManager();

        $followUser = $em->getRepository(User::class)->findUser($usernameOrId);

        if(!$followUser INSTANCEOF User){
            return $this->respondWithErrors([], null, 404);
        }

        $followRequests = $em->createQueryBuilder()
            ->select('r')
            ->from('App:User\Addons\FollowRequest', 'r')
            ->where('r.requesterId = :userId AND r.requestedId = :followId')
            ->orWhere('r.requestedId = :userId AND r.requesterId = :followId')
            ->setParameter('userId', $this->getUser()->getId())
            ->setParameter('followId', $followUser->getId())
            ->getQuery()->getResult();

        $requests = array_map(function($request) {
            return $request->toArray();
        }, $followRequests);

        $outgoing = current(array_filter($requests, function($request) {
            if($request['requester_id'] === $this->getUser()->getId()){
                return true;
            }

            return false;
        }));

        if(!is_array($outgoing)){
            $outgoing = null;
        }

        $incoming = current(array_filter($requests, function($request) {
            if($request['requested_id'] === $this->getUser()->getId()){
                return true;
            }

            return false;
        }));

        if(!is_array($incoming)){
            $incoming = null;
        }

        return $this->respond([
            'outgoing' => $outgoing,
            'incoming' => $incoming,
        ]);
    }

    /**
     * @Route("/users/follow/request/{requestId}/accept", name="follow_request_accept", methods={"POST"})
     */
    public function accept(Request $request, Follow $followService, $requestId)
    {
        $em = $this->getDoctrine()->getManager();

        $followRequest = $em->getRepository(FollowRequest::class)->findOneBy([
            'id' => $requestId,
            'requestedId' => $this->getUser()->getId(),
        ]);

        if(!$followRequest INSTANCEOF FollowRequest){
            return $this->respondWithErrors([], null, 404);
        }

        $follow = $em->getRepository(\App\Entity\User\Addons\Follow::class)->findOneBy([
            'followedId' => $followRequest->getRequestedId(),
            'followerId' => $followRequest->getRequesterId(),
        ]);

        if($follow INSTANCEOF \App\Entity\User\Addons\Follow){
            $em->remove($followRequest);
            $em->flush();

            return $this->respond([
                'follow' => $follow->toArray(),
            ]);
        }

        $followUser = $em->getRepository(User::class)->findOneBy([
            'id' => $followRequest->getRequestedId()
        ]);

        if(!$followUser INSTANCEOF User || $followUser->getId() !== $this->getUser()->getId()){
            return $this->respondWithErrors([
                'id' => 'This user does not exist.'
            ], null, 404);
        }

        $followUser->getInfo()->setFollowerCount($followUser->getInfo()->getFollowerCount() + 1);

        $follow = new \App\Entity\User\Addons\Follow();
        $follow->setFollowedId($followRequest->getRequestedId());
        $follow->setFollowerId($followRequest->getRequesterId());

        $em->persist($follow);

        $em->remove($followRequest);
        $em->flush();

        return $this->respond([
            'follow' => $follow->toArray(),
        ]);
    }

    /**
     * @Route("/users/follow/request/{requestId}/reject", name="follow_unrequest_reject", methods={"DELETE"})
     */
    public function reject(Request $request, $requestId)
    {
        $em = $this->getDoctrine()->getManager();

        $followRequest = $em->getRepository(FollowRequest::class)->findOneBy([
            'id' => $requestId,
            'requestedId' => $this->getUser()->getId(),
        ]);

        if($followRequest INSTANCEOF FollowRequest){
            $em->remove($followRequest);
            $em->flush();
        }else{
            return $this->respondWithErrors([], null, 404);
        }

        return $this->respond([]);
    }
}