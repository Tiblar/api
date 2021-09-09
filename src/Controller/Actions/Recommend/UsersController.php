<?php

namespace App\Controller\Actions\Recommend;

use App\Controller\ApiController;
use App\Service\Recommend\Generator;
use App\Structure\User\SanitizedUser;
use App\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends ApiController
{
    /**
     * @Route("/social/recommend/users", name="social_recommend_users", methods={"GET"})
     */
    public function profile(Request $request, Generator $generator)
    {
        $users = $generator->people();

        return $this->respond([
            'users' => $users
        ]);
    }
}