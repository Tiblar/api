<?php

namespace App\Controller\Actions\Video\Feed;

use App\Controller\ApiController;
use App\Service\Post\Retrieve\Fetch\Video\Newest;
use App\Service\Post\Retrieve\Fetch\Video\Trending;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TrendingController extends ApiController
{
    /**
     * @Route("/video/feed/trending", name="videos_trending")
     */
    public function trending(Request $request, Trending $fetch)
    {
        $limit = $request->query->get('limit');
        $offset = $request->query->get('offset');

        return $this->respond([
            'videos' => $fetch->trending($limit, $offset)
        ]);
    }
}