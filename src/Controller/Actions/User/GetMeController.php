<?php

namespace App\Controller\Actions\User;

use App\Controller\ApiController;
use App\Entity\User\Addons\Follow;
use App\Entity\User\Addons\FollowRequest;
use App\Entity\User\Addons\Privacy;
use App\Service\User\Block;
use App\Service\User\GetMe;
use App\Entity\User\User;
use App\Service\User\Notifier;
use App\Structure\User\SanitizedFollowRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GetMeController extends ApiController
{
    /**
     * @Route("/users/@me", name="get_me", methods={"GET"})
     */
    public function profile(Request $request, GetMe $me)
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

        return $this->respond($me->toArray($user));
    }

    /**
     * @Route("/users/@me/blocked", name="get_me_blocked", methods={"GET"})
     */
    public function blocked(Request $request, Block $blockService)
    {
        $em = $this->getDoctrine()->getManager();

        $block = $blockService->get($this->getUser()->getId());

        $users = $em->getRepository(User::class)
            ->findSanitizedUsers($block->getBlocking(), null, $block);

        $array = array_map(function ($user) {
            return $user->toArray();
        }, $users);

        return $this->respond([
            'users' => $array
        ]);
    }

    /**
     * @Route("/users/@me/blocked/{usernameOrId}", name="get_me_blocked_user", methods={"GET"})
     */
    public function blockedUser(Request $request, Block $blockService, $usernameOrId)
    {
        $em = $this->getDoctrine()->getManager();

        $block = $blockService->get($this->getUser()->getId());

        $users = $em->getRepository(User::class)
            ->findSanitizedUsers($block->getBlocking(), null, $block);

        $array = array_filter($users, function ($user) use ($usernameOrId) {
            if($user->getInfo()->getId() === $usernameOrId || $user->getInfo()->getUsername() === $usernameOrId){
                return true;
            }

            return false;
        });

        return $this->respond([
            'is_blocked' => count($array) !== 0
        ]);
    }

    /**
     * @Route("/users/@me/privacy", name="get_me_privacy", methods={"GET"})
     */
    public function privacy(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $privacy = $em->getRepository(Privacy::class)
            ->findOneBy([
                'userId' => $this->getUser()->getId(),
            ]);

        if(!$privacy INSTANCEOF Privacy){
            $privacy = new Privacy();
            $privacy->setUserId($this->getUser()->getId());

            $em->persist($privacy);

            $user = $em->getRepository(User::class)
                ->findOneBy([
                    'id' => $this->getUser()->getId(),
                ]);

            $user->setPrivacy($privacy);

            $em->flush();
        }

        return $this->respond([
            'privacy' => $privacy->toArray(),
        ]);
    }

    /**
     * @Route("/users/@me/follow/requests", name="follow_requests", methods={"GET"})
     */
    public function requests(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $followRequests = $em->createQueryBuilder()
            ->select('r')
            ->from('App:User\Addons\FollowRequest', 'r')
            ->where('r.requesterId = :userId')
            ->orWhere('r.requestedId = :userId')
            ->setParameter('userId', $this->getUser()->getId())
            ->getQuery()
            ->getArrayResult();

        $userIds = array_unique(array_merge(
            array_column($followRequests, 'requesterId'),
            array_column($followRequests, 'requestedId')
        ));

        $users = $em->getRepository(User::class)
            ->findUsers($userIds);

        $requests = array_map(function($request) use($users) {
            $sanitized = new SanitizedFollowRequest($request);

            foreach($users as $user){
                if($user->getId() === $request['requestedId']){
                    $sanitized->setRequested($user);
                }

                if($user->getId() === $request['requesterId']){
                    $sanitized->setRequester($user);
                }
            }

            return $sanitized->toArray();

        }, $followRequests);

        $outgoing = array_filter($requests, function($request) {
            if($request['requester']['id'] === $this->getUser()->getId()){
                return true;
            }

            return false;
        });

        $incoming = array_filter($requests, function($request) {
            if($request['requested']['id'] === $this->getUser()->getId()){
                return true;
            }

            return false;
        });

        return $this->respond([
            'outgoing' => $outgoing,
            'incoming' => $incoming,
        ]);
    }
}