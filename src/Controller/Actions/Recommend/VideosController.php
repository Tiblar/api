<?php

namespace App\Controller\Actions\Recommend;

use App\Controller\ApiController;
use App\Service\Recommend\Generator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class VideosController extends ApiController
{
    /**
     * @Route("/video/recommend/videos/{postId}", name="video_recommend_videos", methods={"GET"})
     */
    public function trending(Request $request, Generator $generator, $postId)
    {
        $videos = $generator->sidebarVideos($postId);

        return $this->respond([
            'posts' => $videos
        ]);
    }
}