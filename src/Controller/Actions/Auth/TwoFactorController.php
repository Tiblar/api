<?php

namespace App\Controller\Actions\Auth;

use App\Controller\ApiController;
use App\Entity\User\ActionLog;
use App\Entity\User\Addons\ConfirmEmail;
use App\Entity\User\Addons\Disable2FAEmail;
use App\Entity\User\JwtRefreshToken;
use App\Entity\User\TwoFactor\EmailToken;
use App\Service\Generator\Securimage;
use App\Service\User\Emailer;
use App\Service\User\GetMe;
use App\Entity\User\User;
use App\Service\User\UserLogger;
use App\Service\User\Validator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class TwoFactorController extends ApiController
{
    /**
     * @Route("/auth/two-factor/email", name="two_factor_settings", methods={"POST"})
     */
    public function createTwoFactorEmail(Request $request, Emailer $emailer, UserLogger $logger)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'username' => 'This user does not exist.'
            ], null, 404);
        }

        if(is_null($user->getEmail()) || !is_string($user->getEmail())){
            return $this->respondWithErrors([
                'email' => 'No confirmed email found.'
            ], null, 400);
        }

        $user->setTwoFactor(true);
        $user->setTwoFactorType(User::$TWO_FACTOR_EMAIL);

        $em->flush();

        return $this->respond([]);
    }

    /**
     * @Route("/auth/two-factor/email", name="two_factor_settings_2fa_email_request_delete", methods={"DELETE"})
     */
    public function deleteRequestTwoFactorEmail(Request $request, Emailer $emailer, UserLogger $logger)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $this->getUser()->getId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([
                'username' => 'This user does not exist.'
            ], null, 404);
        }

        if(is_null($user->getEmail()) || !is_string($user->getEmail())){
            return $this->respondWithErrors([
                'email' => 'No confirmed email found.'
            ], null, 400);
        }

        $since = new \DateTime();
        $since->modify("-1 minute");

        $count = $logger->count([ActionLog::$TWO_FACTOR_DISABLE_EMAIL_REQUEST], $since, [
            'user_id' => $user->getId(),
        ]);

        if($count[ActionLog::$TWO_FACTOR_DISABLE_EMAIL_REQUEST] >= 1){
            return $this->respondWithErrors([], "You have been rate limited.", 429);
        }

        $disable = new Disable2FAEmail();
        $disable->setUserId($user->getId());
        $disable->setCode(\bin2hex(\openssl_random_pseudo_bytes(8)));

        $em->persist($disable);

        $em->flush();

        $emailer->sendDisable2FAEmail($user->getId(), $user);

        $logger->add($user, ActionLog::$TWO_FACTOR_DISABLE_EMAIL_REQUEST);

        return $this->respond([]);
    }

    /**
     * @Route("/auth/two-factor/email/{code}", name="two_factor_settings_2fa_email_delete", methods={"DELETE"})
     */
    public function deleteTwoFactorEmail(Request $request, UserLogger $logger, Securimage $securimage, $code)
    {
        $security_id = $request->query->get('security_id');
        $securityCode = $request->query->get('security_code');

        $em = $this->getDoctrine()->getManager();

        if($securimage->isValid($security_id, $securityCode) == false){
            return $this->respondWithErrors([
                'captcha' => 'Invalid security code.'
            ], 'Security code error.');
        }

        $disable = $em->getRepository(Disable2FAEmail::class)->findOneBy([
            'userId' => $this->getUser()->getId(),
            'code' => $code
        ]);

        if(!$disable INSTANCEOF Disable2FAEmail || $disable->getExpireTimestamp() < new \DateTime()){
            return $this->respondWithErrors([], null, 404);
        }

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $disable->getUserId()
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([], null, 404);
        }

        $user->setTwoFactor(false);
        $user->setTwoFactorType(null);

        $em->remove($disable);

        $em->flush();

        return $this->respond([]);
    }

    /**
     * @Route("/auth/two-factor/login/{code}", name="two_factor_settings_2fa_login", methods={"POST"})
     */
    public function loginTwoFactorLink(Request $request, UserLogger $logger, JWTTokenManagerInterface $JWTManager, $code)
    {
        $em = $this->getDoctrine()->getManager();

        $emailToken = $em->getRepository(EmailToken::class)->findOneBy([
           'code' => $code,
        ]);

        if(!$emailToken INSTANCEOF EmailToken || $emailToken->getExpireTimestamp() < new \DateTime()){
            return $this->respondWithErrors([], "Invalid login code.", 400);
        }

        $user = $em->getRepository(User::class)->findOneBy([
            'id' => $emailToken->getUserId(),
        ]);

        if(!$user INSTANCEOF User){
            return $this->respondWithErrors([], "Invalid login code.", 400);
        }

        $refreshExpireTimestamp = new \DateTime();
        $refreshExpireTimestamp->modify("+30 days");

        $jwtRefreshToken = new JwtRefreshToken();
        $jwtRefreshToken->setUserId($user->getId());
        $jwtRefreshToken->setExpireTimestamp($refreshExpireTimestamp);
        $jwtRefreshToken->setRefreshToken(\bin2hex(\openssl_random_pseudo_bytes(32)));

        $em->persist($jwtRefreshToken);

        $em->remove($emailToken);

        $em->flush();

        $token = $JWTManager->create($user);

        $cookie = new Cookie(
            'AUTH_TOKEN', // Cookie name, should be the same as in config/packages/lexik_jwt_authentication.yaml.
            $token, // cookie value
            time() + $this->getParameter('jwt_ttl'), // expiration
            '/', // path
            null, // domain, null means that Symfony will generate it on its own.
            $this->getParameter('cookie_secure'), // secure
            true, // httpOnly
            false, // raw
            'strict' // same-site parameter, can be 'lax' or 'strict'.
        );

        return $this->respondWithCookies([
            'refresh_token' => $jwtRefreshToken->getRefreshToken(),
            'refresh_expire' => time() + 2592000,
            'token' => $token,
        ], [$cookie]);
    }
}