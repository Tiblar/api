<?php

namespace App\Controller\Actions\Video\Feed;

use App\Controller\ApiController;
use App\Service\Post\Retrieve\Fetch\Video\Following;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FollowingController extends ApiController
{
    /**
     * @Route("/video/feed/following", name="videos_following")
     */
    public function following(Request $request, Following $fetch)
    {
        $limit = $request->query->get('limit');
        $offset = $request->query->get('offset');

        return $this->respond([
            'videos' => $fetch->newest($limit, $offset)
        ]);
    }
}