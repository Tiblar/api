<?php

namespace App\Controller\Actions\Post;

use App\Controller\ApiController;
use App\Entity\Post\Reply;
use App\Service\Post\Retrieve\Fetch\PostReplies;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GetRepliesController extends ApiController
{
    /**
     * @Route("/post/replies/{postId}", name="get_replies", methods={"GET"})
     */
    public function replies(Request $request, PostReplies $fetch, $postId)
    {
        $replies = $fetch->get($postId);

        return $this->respond([
            'replies' => $replies
        ]);
    }
}