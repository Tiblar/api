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

class ResetController extends ApiController
{
    /**
     * @Route("/auth/reset/{code}", name="reset_password_auth", methods={"POST"})
     */
    public function reset(
        Request $request, Securimage $securimage, UserLogger $logger,
        UserPasswordEncoderInterface $encoder, $code
    )
    {
        $password = $request->request->get('password');
        $securityId = $request->request->get('security_id');
        $securityCode = $request->request->get('security_code');

        $em = $this->getDoctrine()->getManager();

        if($securimage->isValid($securityId, $securityCode) == false){
            return $this->respondWithErrors([
                'captcha' => 'Invalid security code.'
            ], 'Security code error.');
        }

        $reset = $em->getRepository(PasswordReset::class)->findOneBy([
            'code' => $code,
        ]);

        if(!$reset INSTANCEOF PasswordReset || $reset->getExpireTimestamp() < new \DateTime()){
            return $this->respondWithErrors([], null, 404);
        }

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $reset->getUserId(),
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([], null, 404);
        }

        $logger->add($user, ActionLog::$RESET_PASSWORD);

        $user->setPassword($encoder->encodePassword($user, $password));

        $em->remove($reset);

        $em->flush();

        return $this->respond([]);
    }
}