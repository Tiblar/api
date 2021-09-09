<?php

namespace App\Controller\Actions\Post\Feed;

use App\Controller\ApiController;
use App\Service\Post\Retrieve\Fetch\Dashboard;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends ApiController
{
    /**
     * @Route("/post/feed/dashboard/newest", METHODS={"GET"}, name="feed_dashboard_newest")
     */
    public function newest(Request $request, Dashboard $fetch)
    {
        $limit = $request->query->get('limit');
        $offset = $request->query->get('offset');

        return $this->respond([
            'posts' => $fetch->newest($limit, $offset)
        ]);
    }

    /**
     * @Route("/post/feed/dashboard/popular", name="feed_dashboard_popular")
     */
    public function popular(Request $request, Dashboard $fetch)
    {
        $limit = $request->query->get('limit');
        $offset = $request->query->get('offset');
        $period = $request->query->get('period');

        return $this->respond([
            'posts' => $fetch->popular($limit, $offset, $period)
        ]);
    }
}