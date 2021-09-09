<?php

namespace App\Controller\Actions\Post\Feed;

use App\Controller\ApiController;
use App\Service\Post\Retrieve\Fetch\Trending;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TrendingController extends ApiController
{
    /**
     * @Route("/post/feed/trending", name="trending", methods={"GET"})
     */
    public function trending(Request $request, Trending $fetch)
    {
        $limit = $request->query->get('limit');
        $offset = $request->query->get('offset');
        $period = $request->query->get('period');

        return $this->respond([
            'posts' => $fetch->trending($limit, $offset, $period)
        ]);
    }
}