<?php

namespace App\Controller\Actions\Auth;

use App\Controller\ApiController;
use App\Entity\User\User;
use App\Service\Matrix\MatrixInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MatrixLoginTokenController extends ApiController
{
    /**
     * @Route("/auth/matrix/login-token", name="matrix_login_token", methods={"GET"})
     */
    public function loginToken(Request $request, MatrixInterface $matrix)
    {
        $em = $this->getDoctrine()->getManager();

        if(!$matrix->doesUserExist($this->getUser()->getId())){
            $user = $em->getRepository(User::class)->findOneBy([
                'id' => $this->getUser()->getId(),
            ]);

            if(!($user instanceof User)){
                return $this->respondWithErrors([
                    'token' => 'Invalid token.'
                ], 'Authentication error.', 401);
            }

            $avatarContents = file_get_contents("https:" . $user->getInfo()->getAvatar());

            $matrix->createUser($user->getId());

            if(!$matrix->updateUser($user, [ 'avatar_contents' => $avatarContents, 'username' => true ])) {
                return $this->respondWithErrors([
                    'matrix' => 'Error updating Matrix account.'
                ], null, 500);
            }
        }

        $matrixServer = $this->getParameter("matrix")['server'];

        $schema = $this->getParameter('kernel.environment') === 'dev' ? "http://" : "https://";

        $data = $matrix->getUserTokenAndDevice($this->getUser()->getId());

        return $this->respond([
            'access_token' => $data['access_token'],
            'device_id' => $data['device_id'],
            'user_id' => "@tb_" . $this->getUser()->getId() . ":" . $matrixServer,
            'home_server' => $schema . $matrixServer,
        ]);
    }
}