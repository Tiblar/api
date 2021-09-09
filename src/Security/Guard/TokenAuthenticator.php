<?php
declare(strict_types=1);

namespace App\Security\Guard;

use App\Security\User\FormerlyChucksUserProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\PayloadAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\ProviderNotFoundException;
use Symfony\Component\Security\Core\User\ChainUserProvider;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class TokenAuthenticator extends JWTTokenAuthenticator
{
    protected function loadUser(UserProviderInterface $userProvider, array $payload, $identity)
    {
        if ($userProvider instanceof PayloadAwareUserProviderInterface) {
            return $userProvider->loadUserByUsernameAndPayload($identity, $payload);
        }

        if ($userProvider instanceof ChainUserProvider) {
            foreach ($userProvider->getProviders() as $provider) {
                if ($provider instanceof PayloadAwareUserProviderInterface) {
                    return $provider->loadUserByUsernameAndPayload($identity, $payload);
                }
            }
        }

        if(!$userProvider INSTANCEOF FormerlyChucksUserProvider){
            throw new ProviderNotFoundException("You have used a provider that is invalid.");
        }

        return $userProvider->loadUserById($identity);
    }
}