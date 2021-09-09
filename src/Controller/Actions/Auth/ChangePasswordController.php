<?php

namespace App\Controller\Actions\Auth;

use App\Controller\ApiController;
use App\Entity\User\ActionLog;
use App\Entity\User\Addons\PasswordReset;
use App\Entity\User\User;
use App\Service\Generator\Securimage;
use App\Service\User\UserLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Annotation\Route;

class ChangePasswordController extends ApiController
{
    /**
     * @Route("/auth/change-password", name="change_password_auth", methods={"PATCH"})
     */
    public function reset(Request $request, UserLogger $logger, UserPasswordEncoderInterface $encoder)
    {
        $oldPassword = $request->request->get('old_password');
        $newPassword = $request->request->get('new_password');

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId(),
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([], null, 404);
        }

        if(!$encoder->isPasswordValid($user, $oldPassword)){
            return $this->respondWithErrors([], 'Bad credentials.', 401);
        }

        $since = new \DateTime();
        $since->modify("-1 minute");

        $count = $logger->count([ActionLog::$CHANGE_PASSWORD], $since, [
            'user_id' => $user->getId(),
        ]);

        if($count[ActionLog::$CHANGE_PASSWORD] > 2){
            return $this->respondWithErrors([
                'new_password' => 'Please wait a few minutes before changing again.'
            ], null, 429);
        }

        $logger->add($user, ActionLog::$CHANGE_PASSWORD);

        $user->setPassword($encoder->encodePassword($user, $newPassword));

        $em->flush();

        return $this->respond([]);
    }
}