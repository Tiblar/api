<?php
namespace App\Security\User;

use App\Entity\User\User;
use App\Entity\User\UserInfo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class FormerlyChucksUserProvider implements UserProviderInterface
{
    private $em;

    private $parameterBag;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $parameterBag)
    {
        $this->em = $em;
        $this->parameterBag = $parameterBag;
    }

    public function loadUserByUsername($username)
    {
        return $this->fetchUser($username);
    }

    public function loadUserById($id)
    {
        return $this->em->getRepository(User::class)->findOneBy([
            'id' => $id
        ]);
    }

    public function refreshUser(UserInterface $user)
    {
        $username = $user->getUsername();

        return $this->fetchUser($username);
    }

    public function supportsClass($class)
    {
        return true;
    }

    private function fetchUser($username)
    {
        $info = $this->em->getRepository(UserInfo::class)->findOneBy([
            'username' => $username,
        ]);

        if(!$info INSTANCEOF UserInfo){
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $username)
            );
        }

        if(in_array($info->getUsername(), $this->parameterBag->get('reserved_usernames'))){
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $username)
            );
        }

        return $this->em->getRepository(User::class)->findOneBy([
            'id' => $info->getId()
        ]);
    }
}