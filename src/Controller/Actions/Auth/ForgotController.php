<?php

namespace App\Controller\Actions\Auth;

use App\Controller\ApiController;
use App\Entity\User\ActionLog;
use App\Entity\User\Addons\ConfirmEmail;
use App\Entity\User\Addons\PasswordReset;
use App\Entity\User\User;
use App\Exception\RateLimitException;
use App\Service\Generator\Securimage;
use App\Service\User\Emailer;
use App\Service\User\UserLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ForgotController extends ApiController
{
    /**
     * @Route("/auth/forgot", name="forgot_auth", methods={"POST"})
     */
    public function forgot(Request $request, Securimage $securimage, Emailer $emailer, UserLogger $logger)
    {
        $email = $request->request->get('email');
        $securityId = $request->request->get('security_id');
        $securityCode = $request->request->get('security_code');

        $em = $this->getDoctrine()->getManager();

        if($securimage->isValid($securityId, $securityCode) == false){
            return $this->respondWithErrors([
                'captcha' => 'Invalid security code.'
            ], 'Security code error.');
        }

        $since = new \DateTime();
        $since->modify("-1 minute");

        $count = $logger->count([ActionLog::$RESET_PASSWORD_REQUEST], $since, [
            'ip' => true,
        ]);

        if($count[ActionLog::$RESET_PASSWORD_REQUEST] >= 1){
            return $this->respondWithErrors([], "You have been rate limited.", 429);
        }

        $user = $em->getRepository(User::class)->findOneBy([
            'email' => $email,
        ]);

        if(!$user INSTANCEOF User || is_null($user->getEmail())){
            $logger->add(null, ActionLog::$RESET_PASSWORD_REQUEST);

            $emailer->sendFauxEmail();
            return $this->respond([]);
        }

        $logger->add($user, ActionLog::$RESET_PASSWORD_REQUEST);

        $reset = new PasswordReset();
        $reset->setUserId($user->getId());
        $reset->setCode(\bin2hex(\openssl_random_pseudo_bytes(8)));

        $em->persist($reset);
        $em->flush();

        $emailer->sendPasswordResetEmail($user->getId(), $user);

        return $this->respond([]);
    }
}