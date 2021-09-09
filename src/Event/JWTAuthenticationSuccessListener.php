<?php
namespace App\Event;

use App\Entity\User\JwtRefreshToken;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Class JWTAuthenticationSuccessListener
 * @package App\EventListener
 */
class JWTAuthenticationSuccessListener
{
    /**
     * @var int
     */
    private $tokenLifetime;

    /**
     * @var bool
     */
    private $cookieSecure;

    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    public function __construct(int $tokenLifetime, bool $cookieSecure, EntityManagerInterface $em)
    {
        $this->tokenLifetime = $tokenLifetime;
        $this->cookieSecure = $cookieSecure;
        $this->em = $em;
    }

    /**
     * Sets JWT as a cookie on successful authentication.
     *
     * @param AuthenticationSuccessEvent $event
     * @throws Exception
     */
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $refreshExpireTimestamp = new \DateTime();
        $refreshExpireTimestamp->modify("+30 days");

        $jwtRefreshToken = new JwtRefreshToken();
        $jwtRefreshToken->setUserId($event->getUser()->getUsername());
        $jwtRefreshToken->setExpireTimestamp($refreshExpireTimestamp);
        $jwtRefreshToken->setRefreshToken(\bin2hex(\openssl_random_pseudo_bytes(32)));

        $this->em->persist($jwtRefreshToken);
        $this->em->flush();

        $data = $event->getData();
        $data['refresh_token'] = $jwtRefreshToken->getRefreshToken();
        $data['refresh_expire'] = time() + 2592000;

        $event->setData($data);

        $event->getResponse()->headers->setCookie(
            new Cookie(
                'AUTH_TOKEN', // Cookie name, should be the same as in config/packages/lexik_jwt_authentication.yaml.
                $event->getData()['token'], // cookie value
                time() + $this->tokenLifetime, // expiration
                '/', // path
                null, // domain, null means that Symfony will generate it on its own.
                $this->cookieSecure, // secure
                true, // httpOnly
                false, // raw
                'strict' // same-site parameter, can be 'lax' or 'strict'.
            )
        );
    }
}