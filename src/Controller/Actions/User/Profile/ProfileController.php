<?php

namespace App\Controller\Actions\User\Profile;

use App\Controller\ApiController;
use App\Entity\Analytics\ViewLog;
use App\Entity\User\Addons\Connection;
use App\Http\ApiResponse;
use App\Service\Post\Retrieve\Fetch\Profile;
use App\Service\User\Block;
use App\Service\User\Privacy;
use App\Structure\User\BlockStructure;
use App\Structure\User\FollowStructure;
use App\Structure\User\SanitizedUser;
use App\Entity\User\User;
use App\Entity\User\Addons\Privacy as PrivacyEntity;
use App\Service\User\Follow;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends ApiController
{
    /**
     * @Route("/users/profile/{usernameOrId}", name="get_profile", methods={"GET"})
     */
    public function profile(Request $request, Follow $followService, Block $blockService, Privacy $privacyService, Security $security, $usernameOrId)
    {
        $em = $this->getDoctrine()->getManager();

        $userId = $security->isGranted("ROLE_USER") ? $this->getUser()->getId() : null;

        $follow = $followService->get($userId);
        $block = $blockService->get($userId);

        $user = $em->getRepository(User::class)
            ->findSanitizedUser($usernameOrId, $follow, $block);

        if(!$user INSTANCEOF SanitizedUser){
            return $this->respondWithErrors([
                'username' => 'This user does not exist.'
            ], null, 404);
        }

        $privacy = $privacyService->get($user->getId());

        $denied = $this->checkAccess($userId, $user, $privacy, $follow, $block);
        if($denied INSTANCEOF ApiResponse){
            return $denied;
        }

        $connections = $em->getRepository(Connection::class)->findBy([
            'userId' => $user->getId(),
        ]);

        foreach($connections as $connection){
            $user->addConnection($connection->toArray());
        }

        $clientIp = $request->getClientIp();

        $hasCloudflare = $request->headers->has("X-Forwarded-For-Formerly-Chucks");

        $ipAddress = null;
        if($hasCloudflare){
            $ipAddress = $request->headers->get("X-Forwarded-For-Formerly-Chucks");
        }

        if(is_null($ipAddress)){
            $ipAddress = $clientIp;
        }

        $em->getRepository(ViewLog::class)
            ->addViews([$user->getId()], [$user->getId() => $user->getId()], ViewLog::$VIEW_SOURCE_PROFILE, $ipAddress, ViewLog::$VIEW_TYPE_USER);

        return $this->respond([
            'user' => $user->toArray(),
        ]);
    }

    /**
     * @Route("/users/profile/{usernameOrId}/feed", name="get_profile_feed", methods={"GET"})
     */
    public function feed(
        Request $request, Follow $followService, Block $blockService, Privacy $privacyService, Security $security, Profile $fetch, $usernameOrId
    ){
        $limit = $request->query->get('limit');
        $offset = $request->query->get('offset');
        $query = $request->query->get('q');

        $em = $this->getDoctrine()->getManager();
        $userId = $security->isGranted("ROLE_USER") ? $this->getUser()->getId() : null;

        $follow = $followService->get($userId);
        $block = $blockService->get($userId);

        $user = $em->getRepository(User::class)
            ->findSanitizedUser($usernameOrId);

        if(!$user INSTANCEOF SanitizedUser){
            return $this->respondWithErrors([
                'username' => 'This user does not exist.'
            ], null, 404);
        }

        $privacy = $privacyService->get($user->getId());

        $denied = $this->checkAccess($userId, $user, $privacy, $follow, $block);
        if($denied INSTANCEOF ApiResponse){
            return $denied;
        }

        return $this->respond([
            'posts' => $fetch->newest($user->getId(), $limit, $offset, $query)
        ]);
    }

    /**
     * @Route("/users/profile/{usernameOrId}/videos", name="get_profile_video", methods={"GET"})
     */
    public function video(
        Request $request, Follow $followService, Block $blockService, Privacy $privacyService, Security $security, Profile $fetch, $usernameOrId
    ){
        $limit = $request->query->get('limit');
        $offset = $request->query->get('offset');
        $query = $request->query->get('q');

        $em = $this->getDoctrine()->getManager();
        $userId = $security->isGranted("ROLE_USER") ? $this->getUser()->getId() : null;

        $follow = $followService->get($userId);
        $block = $blockService->get($userId);

        $user = $em->getRepository(User::class)
            ->findSanitizedUser($usernameOrId);

        if(!$user INSTANCEOF SanitizedUser){
            return $this->respondWithErrors([
                'username' => 'This user does not exist.'
            ], null, 404);
        }

        $privacy = $privacyService->get($user->getId());

        $denied = $this->checkAccess($userId, $user, $privacy, $follow, $block);
        if($denied INSTANCEOF ApiResponse){
            return $denied;
        }

        return $this->respond([
            'videos' => $fetch->videos($user->getId(), $limit, $offset, $query)
        ]);
    }

    /**
     * @Route("/users/profile/{usernameOrId}/following", name="get_profile_following", methods={"GET"})
     */
    public function following(
        Request $request, Follow $followService, Block $blockService, Privacy $privacyService, Security $security, $usernameOrId
    ){
        $em = $this->getDoctrine()->getManager();
        $userId = $security->isGranted("ROLE_USER") ? $this->getUser()->getId() : null;

        $offset = $request->query->get('offset');
        if(is_null($offset)){
            $offset = 0;
        }

        $limit = $request->query->get('limit');
        if($limit < 10 || $limit > 100){
            $limit = 10;
        }

        $follow = $followService->get($userId);
        $block = $blockService->get($userId);

        $user = $em->getRepository(User::class)
            ->findSanitizedUser($usernameOrId);

        if(!$user INSTANCEOF SanitizedUser){
            return $this->respondWithErrors([
                'username' => 'This user does not exist.'
            ], null, 404);
        }

        $privacy = $privacyService->get($user->getId());

        if(!$privacy->getFollowing() && $userId !== $user->getId()){
            return $this->respondWithErrors([], null, 404);
        }

        $denied = $this->checkAccess($userId, $user, $privacy, $follow, $block);
        if($denied INSTANCEOF ApiResponse){
            return $denied;
        }

        $follow = $followService->get($user->getId(), $offset, $limit);

        if(!$follow INSTANCEOF FollowStructure){
            return $this->respond([
                'following' => [],
            ]);
        }

        $users = $em->getRepository(User::class)
            ->findSanitizedUsers($follow->getFollowing());

        $users = array_map(function($user) {
            return $user->toArray();
        }, $users);

        return $this->respond([
            'following' => $users
        ]);
    }

    /**
     * @Route("/users/profile/{usernameOrId}/followers", name="get_profile_followers", methods={"GET"})
     */
    public function followers(
        Request $request, Follow $followService, Block $blockService, Privacy $privacyService, Security $security, $usernameOrId
    ){
        $em = $this->getDoctrine()->getManager();
        $userId = $security->isGranted("ROLE_USER") ? $this->getUser()->getId() : null;

        $offset = $request->query->get('offset');
        if(is_null($offset)){
            $offset = 0;
        }

        $limit = $request->query->get('limit');
        if($limit < 10 || $limit > 100){
            $limit = 10;
        }

        $follow = $followService->get($userId);
        $block = $blockService->get($userId);

        $user = $em->getRepository(User::class)
            ->findSanitizedUser($usernameOrId);

        if(!$user INSTANCEOF SanitizedUser){
            return $this->respondWithErrors([
                'username' => 'This user does not exist.'
            ], null, 404);
        }

        $privacy = $privacyService->get($user->getId());

        $denied = $this->checkAccess($userId, $user, $privacy, $follow, $block);
        if($denied INSTANCEOF ApiResponse){
            return $denied;
        }

        $follow = $followService->get($user->getId(), $offset, $limit);

        if(!$follow INSTANCEOF FollowStructure){
            return $this->respond([
                'followers' => [],
            ]);
        }

        $users = $em->getRepository(User::class)
            ->findSanitizedUsers($follow->getFollowers());


        $users = array_map(function($user) {
            return $user->toArray();
        }, $users);

        return $this->respond([
            'followers' => $users
        ]);
    }

    /**
     * @Route("/users/profile/{usernameOrId}/likes/social", name="get_profile_likes_social", methods={"GET"})
     */
    public function likes_social(
        Request $request, Follow $followService, Block $blockService, Privacy $privacyService, Security $security, Profile $fetch, $usernameOrId
    ){
        $limit = $request->query->get('limit');
        $offset = $request->query->get('offset');

        $em = $this->getDoctrine()->getManager();
        $userId = $security->isGranted("ROLE_USER") ? $this->getUser()->getId() : null;

        $follow = $followService->get($userId);
        $block = $blockService->get($userId);

        $user = $em->getRepository(User::class)
            ->findSanitizedUser($usernameOrId);

        if(!$user INSTANCEOF SanitizedUser){
            return $this->respondWithErrors([
                'username' => 'This user does not exist.'
            ], null, 404);
        }

        $privacy = $privacyService->get($user->getId());

        if(!$privacy->getLikes() && $userId !== $user->getId()){
            return $this->respondWithErrors([], null, 404);
        }

        $denied = $this->checkAccess($userId, $user, $privacy, $follow, $block);
        if($denied INSTANCEOF ApiResponse){
            return $denied;
        }

        $likes = $fetch->socialLikes($user->getId(), $limit, $offset);

        return $this->respond([
            'likes' => $likes
        ]);
    }

    /**
     * @Route("/users/profile/{usernameOrId}/likes/video", name="get_profile_likes_video", methods={"GET"})
     */
    public function likes_video(
        Request $request, Follow $followService, Block $blockService, Privacy $privacyService, Security $security, Profile $fetch, $usernameOrId
    ){
        $limit = $request->query->get('limit');
        $offset = $request->query->get('offset');

        $em = $this->getDoctrine()->getManager();
        $userId = $security->isGranted("ROLE_USER") ? $this->getUser()->getId() : null;

        $follow = $followService->get($userId);
        $block = $blockService->get($userId);

        $user = $em->getRepository(User::class)
            ->findSanitizedUser($usernameOrId);

        if(!$user INSTANCEOF SanitizedUser){
            return $this->respondWithErrors([
                'username' => 'This user does not exist.'
            ], null, 404);
        }

        $privacy = $privacyService->get($user->getId());

        if(!$privacy->getLikes() && $userId !== $user->getId()){
            return $this->respondWithErrors([], null, 404);
        }

        $denied = $this->checkAccess($userId, $user, $privacy, $follow, $block);
        if($denied INSTANCEOF ApiResponse){
            return $denied;
        }

        $likes = $fetch->videoLikes($user->getId(), $limit, $offset);

        return $this->respond([
            'likes' => $likes
        ]);
    }

    /**
     * @Route("/users/profile/{usernameOrId}/cards", name="get_profile_cards", methods={"GET"})
     */
    public function cards(
        Request $request, Follow $followService, Block $blockService, Privacy $privacyService, Security $security, $usernameOrId
    ){
        return $this->respond([
            'cards' => []
        ]);
    }

    private function checkAccess(
        ?string $userId, SanitizedUser $user, PrivacyEntity $privacy, FollowStructure $follow, BlockStructure $block
    ): ?ApiResponse {
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

        if(
            $user->isBanned()
        ){
            return $this->respondWithErrors([
                'banned' => 'This user has been banned.'
            ], "This user has been banned.", 403);
        }

        return null;
    }
}