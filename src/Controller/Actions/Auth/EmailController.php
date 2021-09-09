<?php

namespace App\Controller\Actions\Auth;

use App\Controller\ApiController;
use App\Entity\User\ActionLog;
use App\Entity\User\Addons\ConfirmEmail;
use App\Entity\User\User;
use App\Exception\RateLimitException;
use App\Service\Generator\Securimage;
use App\Service\User\Emailer;
use App\Service\User\UserLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class EmailController extends ApiController
{
    /**
     * @Route("/auth/confirm-email", name="delete_confirm_email", methods={"DELETE"})
     */
    public function delete(Request $request, Emailer $emailer)
    {
        $emailer->cancelConfirmationEmail($this->getUser()->getId());

        return $this->respond([]);
    }

    /**
     * @Route("/auth/confirm-email/resend", name="resend_confirm_email", methods={"GET"})
     */
    public function resend(Request $request, Emailer $emailer, UserLogger $logger)
    {
        $em = $this->getDoctrine()->getManager();

        $since = new \DateTime();
        $since->modify("-1 minute");

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId(),
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([], null, 404);
        }

        $count = $logger->count([ActionLog::$RESEND_EMAIL_CONFIRMATION], $since, [
            'user_id' => $user->getId(),
        ]);

        if($count[ActionLog::$RESEND_EMAIL_CONFIRMATION] >= 1){
            return $this->respondWithErrors([], "You have been rate limited.", 429);
        }

        $logger->add($user, ActionLog::$RESEND_EMAIL_CONFIRMATION);

        try{
            $emailer->sendConfirmationEmail($this->getUser()->getId());
        }catch (NotFoundHttpException $e){
            return $this->respondWithErrors([
                'email' => 'Confirmation not found.'
            ], null, 404);
        }

        return $this->respond([]);
    }

    /**
     * @Route("/auth/confirm-email/{code}", name="confirm_email", methods={"GET"})
     */
    public function confirm(Request $request, Securimage $securimage, $code)
    {
        $security_id = $request->query->get('security_id');
        $securityCode = $request->query->get('security_code');

        $em = $this->getDoctrine()->getManager();

        if($securimage->isValid($security_id, $securityCode) == false){
            return $this->respondWithErrors([
                'captcha' => 'Invalid security code.'
            ], 'Security code error.');
        }

        $confirm = $em->getRepository(ConfirmEmail::class)->findOneBy([
            'code' => $code,
        ]);

        if(!$confirm INSTANCEOF ConfirmEmail || $confirm->getExpireTimestamp() < new \DateTime()){
            return $this->respondWithErrors([], null, 404);
        }

        $user = $em->getRepository(User::class)->findOneBy([
           'id' => $confirm->getUserId(),
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([], null, 404);
        }

        $user->setEmail($confirm->getEmail());
        $user->setConfirmEmail(null);

        $em->remove($confirm);

        $em->flush();

        return $this->respond([]);
    }
}