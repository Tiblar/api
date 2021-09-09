<?php

namespace App\Controller\Actions\Post\Feed;

use App\Controller\ApiController;
use App\Service\Post\Retrieve\Fetch\Newest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class NewestController extends ApiController
{
    /**
     * @Route("/post/feed/newest", name="newest")
     */
    public function newest(Request $request, Newest $fetch)
    {
        $limit = $request->query->get('limit');
        $offset = $request->query->get('offset');

        return $this->respond([
            'posts' => $fetch->newest($limit, $offset)
        ]);
    }
}