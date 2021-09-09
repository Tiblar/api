<?php

namespace App\Controller\Actions\Recommend;

use App\Controller\ApiController;
use App\Service\Recommend\Generator;
use App\Structure\User\SanitizedUser;
use App\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TagsController extends ApiController
{
    /**
     * @Route("/social/recommend/tags/interesting", name="social_recommend_tags_interesting", methods={"GET"})
     */
    public function interesting(Request $request, Generator $generator)
    {
        $tags = $generator->interestingTags();

        return $this->respond([
            'tags' => $tags
        ]);
    }

    /**
     * @Route("/social/recommend/tags/trending", name="social_recommend_tags_trending", methods={"GET"})
     */
    public function trending(Request $request, Generator $generator)
    {
        $tags = $generator->trendingTags();

        return $this->respond([
            'tags' => $tags
        ]);
    }
}