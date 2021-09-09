<?php

namespace App\Controller\Actions\Search;

use App\Controller\ApiController;
use App\Entity\User\User;
use App\Service\Search\Video;
use App\Structure\User\SanitizedUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchVideoController extends ApiController
{
    /**
     * @Route("/search/video", name="search_video", methods={"GET"})
     */
    public function searchVideo(Request $request, Video $video)
    {
        $query = urldecode($request->query->get('query'));
        $limit = $request->query->get('limit');
        $offset = $request->query->get('offset');

        return $this->respond([
            'videos' => $video->popular($query, $limit, $offset),
        ]);
    }

    /**
     * @Route("/search/video/complete", name="search_video_complete", methods={"GET"})
     * @Route("/search/video/{usernameOrId}/complete", name="search_video_complete_user", methods={"GET"})
     */
    public function complete(Request $request, Video $video, $usernameOrId = null)
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
            'tags' => $video->tags($query, $userId),
            'titles' => $video->titles($query, $userId),
            'profiles' => [],
        ];

        if(is_null($userId)){
            $complete['profiles'] = $video->profile($query);
        }

        return $this->respond($complete);
    }
}