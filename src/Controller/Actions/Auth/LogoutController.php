<?php

namespace App\Controller\Actions\Auth;

use App\Controller\ApiController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LogoutController extends ApiController
{
    /**
     * @Route("/auth/logout/", name="logout", methods={"POST"})
     */
    public function logout(Request $request)
    {
        return new Response();
    }
}