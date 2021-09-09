<?php
namespace App\Event;

use App\Entity\User\ActionLog;
use App\Entity\User\TwoFactor\EmailToken;
use App\Entity\User\User;
use App\Service\Generator\Securimage;
use App\Service\User\Emailer;
use App\Service\User\UserLogger;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Doctrine\ORM\EntityManager;

class LoginListener
{
    protected $em;

    protected $securimage;

    protected $emailer;

    protected $logger;

    public function __construct(EntityManager $em, Securimage $securimage, Emailer $emailer, UserLogger $logger)
    {
        $this->em = $em;
        $this->securimage = $securimage;
        $this->emailer = $emailer;
        $this->logger = $logger;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $request = $event->getRequest();
        $route = $request->get('_route');
        $interface = $event->getAuthenticationToken()->getUser();

        if (
            in_array($route, ["api_login_check", "user_registration"]) &&
            $this->securimage->isValid($request->request->get('security_id'), $request->request->get('security_code')) == false
        ) {
            throw new BadCredentialsException("Invalid security code.");
        }


        if($interface->isBanned()){
            throw new BadCredentialsException("You are banned.");
        }

        if (in_array($route, ["api_login_check", "user_registration"])) {
            $user = $this->em->getRepository(User::class)->findOneBy([
                'id' => $interface->getId()
            ]);

            if($user INSTANCEOF User && $user->isTwoFactor() && $user->getTwoFactorType() === User::$TWO_FACTOR_EMAIL){
                $emailToken = new EmailToken();
                $emailToken->setUserId($user->getId());
                $emailToken->setCode(\bin2hex(\openssl_random_pseudo_bytes(8)));

                $this->em->persist($emailToken);
                $this->em->flush();

                $this->emailer->sendLoginLinkEmail($user->getId(), $user);

                throw new BadCredentialsException("A login link has been sent to your email.");
            }

            if($route === "api_login_check"){
                $this->logger->add($user, ActionLog::$AUTH_LOGIN, [
                    'user_id' => $user->getId(),
                ]);
            }
        }
    }
}
