<?php

namespace App\Controller\Actions;

use App\Controller\ApiController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PingController extends ApiController
{
    /**
     * @Route("/ping", name="ping")
     */
    public function ping(Request $request)
    {
        return $this->respond([]);
    }
}