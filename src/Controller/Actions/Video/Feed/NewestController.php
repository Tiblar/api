<?php

namespace App\Controller\Actions\Video\Feed;

use App\Controller\ApiController;
use App\Service\Post\Retrieve\Fetch\Video\Newest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class NewestController extends ApiController
{
    /**
     * @Route("/video/feed/newest", name="videos_newest")
     */
    public function newest(Request $request, Newest $fetch)
    {
        $limit = $request->query->get('limit');
        $offset = $request->query->get('offset');

        return $this->respond([
            'videos' => $fetch->newest($limit, $offset)
        ]);
    }
}