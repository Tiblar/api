<?php

namespace App\Controller\Actions\Video;

use App\Controller\ApiController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GetCategoriesController extends ApiController
{
    /**
     * @Route("/video/categories", name="video_categories", methods={"GET"})
     */
    public function categories(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->createQueryBuilder()
            ->select('c')
            ->from('App:Video\Category', 'c')
            ->getQuery()
            ->getArrayResult();

        return $this->respond(['categories' => $categories]);
    }
}