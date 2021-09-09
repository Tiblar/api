<?php

namespace App\Controller\Actions\Auth;

use App\Controller\ApiController;
use App\Entity\User\ActionLog;
use App\Entity\User\Addons\ConfirmEmail;
use App\Service\Matrix\MatrixInterface;
use App\Service\User\Emailer;
use App\Service\User\GetMe;
use App\Entity\User\User;
use App\Service\User\UserLogger;
use App\Service\User\Validator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends ApiController
{
    /**
     * @Route("/auth/account", name="update_account_auth", methods={"PATCH"})
     */
    public function account(Request $request, UserPasswordEncoderInterface $passwordEncoder, GetMe $me,
                            Validator $validator, Emailer $emailer, UserLogger $logger, MatrixInterface $matrix)
    {
        $em = $this->getDoctrine()->getManager();

        $username = $request->request->get('username');
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'username' => 'This user does not exist.'
            ], null, 404);
        }

        if(!$passwordEncoder->isPasswordValid($user, $password)){
            return $this->respondWithErrors([], 'Bad credentials.', 401);
        }

        $usernameValidator = $validator->username($username);
        if($usernameValidator !== true && $user->getInfo()->getUsername() !== $username){
            return $this->respondWithErrors([
                'username' => $usernameValidator
            ]);
        }

        if(!is_string($email) || strlen($email) === 0 || empty($email)){
            $email = null;
        }

        if($user->isTwoFactor() && $user->getTwoFactorType() === User::$TWO_FACTOR_EMAIL){
            return $this->respondWithErrors([
                'email' => 'You must disable two factor authentication to change your email.'
            ], null, 403);
        }

        $emailValidator = $validator->email($email);
        if($emailValidator !== true && !is_null($email) && $user->getEmail() !== $email){
            return $this->respondWithErrors([
                'email' => $emailValidator
            ]);
        }

        $since = new \DateTime();
        $since->modify("-1 day");

        $count = $logger->count([ActionLog::$CHANGE_USERNAME, ActionLog::$CHANGE_EMAIL], $since, [
            'user_id' => $user->getId(),
        ]);

        if($user->getInfo()->getUsername() !== $username && $count[ActionLog::$CHANGE_USERNAME] >= 3){
            return $this->respondWithErrors([
                'username' => 'You can change your username only 3 times per day.'
            ], null, 429);
        }

        if(!is_null($email) && $user->getEmail() !== $email && $count[ActionLog::$CHANGE_EMAIL] >= 3){
            return $this->respondWithErrors([
                'email' => 'You can change your email only 3 times per day.'
            ], null, 429);
        }

        if($user->getInfo()->getUsername() !== $username){
            $logger->add($user, ActionLog::$CHANGE_USERNAME, [
                'from' => $user->getInfo()->getUsername(),
                'to' => $username,
            ]);
            $user->getInfo()->setUsername($username);

            if(!$matrix->updateUser($user, [ 'username' => true ])) {
                return $this->respondWithErrors([
                    'matrix' => 'Error updating Matrix account.'
                ], null, 500);
            }
        }

        if(is_null($email) && $user->getEmail() !== $email){
            $logger->add($user, ActionLog::$CHANGE_EMAIL, [
                'from' => $user->getEmail(),
                'to' => $email,
            ]);

            $user->setEmail($email);
            $user->setConfirmEmail(null);

            $emailer->cancelConfirmationEmail($user->getId());
        }

        if(is_string($email) && $user->getEmail() !== $email){
            $logger->add($user, ActionLog::$CHANGE_EMAIL, [
                'from' => $user->getEmail(),
                'to' => $email,
            ]);

            $emailer->cancelConfirmationEmail($user->getId());

            $confirm = new ConfirmEmail();
            $confirm->setUserId($user->getId());
            $confirm->setEmail($email);
            $confirm->setCode(\bin2hex(\openssl_random_pseudo_bytes(8)));

            $em->persist($confirm);

            $user->setConfirmEmail($confirm);
        }

        $em->flush();

        if(is_string($email) && $user->getEmail() !== $email){
            $emailer->sendConfirmationEmail($user->getId());
        }

        return $this->respond($me->toArray($user));
    }
}