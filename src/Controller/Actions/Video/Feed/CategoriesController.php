<?php

namespace App\Controller\Actions\Video\Feed;

use App\Controller\ApiController;
use App\Service\Post\Retrieve\Fetch\Video\Categories;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CategoriesController extends ApiController
{
    /**
     * @Route("/video/feed/category/{category}/newest", name="videos_category_newest")
     */
    public function newest(Request $request, Categories $fetch, $category)
    {
        $limit = $request->query->get('limit');
        $offset = $request->query->get('offset');

        return $this->respond([
            'videos' => $fetch->newest($category, $limit, $offset)
        ]);
    }

    /**
     * @Route("/video/feed/category/{category}/trending", name="videos_category_trending")
     */
    public function trending(Request $request, Categories $fetch, $category)
    {
        $limit = $request->query->get('limit');
        $offset = $request->query->get('offset');

        return $this->respond([
            'videos' => $fetch->trending($category, $limit, $offset)
        ]);
    }
}