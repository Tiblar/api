<?php

namespace App\Controller\Actions\Search;

use App\Controller\ApiController;
use App\Entity\User\User;
use App\Service\Search\Social;
use App\Structure\User\SanitizedUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchSocialController extends ApiController
{
    /**
     * @Route("/search/social/newest", name="search_social_newest", methods={"GET"})
     */
    public function searchNewest(Request $request, Social $social, $userId = null)
    {
        $query = urldecode($request->query->get('query'));
        $limit = $request->query->get('limit');
        $offset = $request->query->get('offset');
        $type = $request->query->get('type');
        $nsfw = filter_var($request->query->get('nsfw'), FILTER_VALIDATE_BOOLEAN);

        return $this->respond([
            'posts' => $social->newest($query, $limit, $nsfw, $type, $offset),
        ]);
    }

    /**
     * @Route("/search/social/popular", name="search_social_popular", methods={"GET"})
     */
    public function searchPopular(Request $request, Social $social, $userId = null)
    {
        $query = urldecode($request->query->get('query'));
        $limit = $request->query->get('limit');
        $offset = $request->query->get('offset');
        $period = $request->query->get('period');
        $type = $request->query->get('type');
        $nsfw = filter_var($request->query->get('nsfw'), FILTER_VALIDATE_BOOLEAN);

        return $this->respond([
            'posts' => $social->popular($query, $limit, $nsfw, $type, $offset, $period),
        ]);
    }

    /**
     * @Route("/search/social/profiles", name="search_social_profiles", methods={"GET"})
     */
    public function searchProfiles(Request $request, Social $social)
    {
        $query = urldecode($request->query->get('username'));

        return $this->respond([
            'profiles' => $social->profile($query),
        ]);
    }

    /**
     * @Route("/search/social/complete", name="search_social_complete", methods={"GET"})
     * @Route("/search/social/{usernameOrId}/complete", name="search_social_complete_user", methods={"GET"})
     */
    public function complete(Request $request, Social $social, $usernameOrId = null)
    {
        $em = $this->getDoctrine()->getManager();

        $userId = null;
        if(!is_null($usernameOrId)){
            $user = $em->getRepository(User::class)
                ->findSanitizedUser($usernameOrId);

            if($user instanceof SanitizedUser){
                $userId = $user->getId();
            }
        }

        $query = urldecode($request->query->get('query'));

        $complete = [
            'tags' => $social->tags($query, $userId),
            'titles' => $social->titles($query, $userId),
            'profiles' => [],
        ];

        if(is_null($userId)){
            $complete['profiles'] = $social->profile($query);
        }

        return $this->respond($complete);
    }
}