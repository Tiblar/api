<?php

namespace App\Controller\Staff\Sync;

use App\Controller\ApiController;
use App\Service\Post\Retrieve\Fetch\Staff;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PostsController extends ApiController
{
    /**
     * @Route("/sync/posts", name="staff_api_sync_posts", methods={"GET"})
     */
    public function posts(Request $request, Staff $fetch)
    {
        $limit = $request->query->get('limit');
        $afterId = $request->query->get('after');

        return $this->respond([
            'posts' => $fetch->posts($limit, $afterId)
        ]);
    }

    /**
     * @Route("/sync/posts/reports", name="staff_api_sync_posts_reports", methods={"GET"})
     */
    public function reports(Request $request, Staff $fetch)
    {
        $limit = $request->query->get('limit');
        $afterId = $request->query->get('after');

        return $this->respond(
            $fetch->postReports($limit, $afterId)
        );
    }
}