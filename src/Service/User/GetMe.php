<?php
namespace App\Service\User;

use App\Entity\User\Addons\ConfirmEmail;
use App\Entity\User\User;
use App\Structure\User\SanitizedConfirmEmail;
use App\Structure\User\SanitizedUser;
use Doctrine\ORM\EntityManagerInterface;

class GetMe
{
    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param User $user
     * @return array
     */
    public function toArray(User $user): array
    {
        $connections = $this->em->createQueryBuilder()
            ->select('c.service, c.account, c.link')
            ->from('App:User\Addons\Connection', 'c')
            ->where('c.userId = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getArrayResult();

        $sanitizedUser = new SanitizedUser($user, true);

        $sanitizedUser->setConnections($connections);

        $array = $sanitizedUser->toArray();
        $array['email'] = $user->getEmail();

        $confirmEmail = $user->getConfirmEmail();

        if($confirmEmail INSTANCEOF ConfirmEmail){
            $confirm = new SanitizedConfirmEmail($confirmEmail);
            $array['confirm_email'] = $confirm->toArray();
        }else{
            $array['confirm_email'] = null;
        }

        $array['nsfw_filter'] = $user->getNsfwFilter();
        $array['theme'] = $user->getTheme();
        $array['storage'] = $user->getStorage();
        $array['storage_limit'] = $user->getStorageLimit();
        $array['two_factor'] = $user->isTwoFactor();
        $array['two_factor_type'] = $user->getTwoFactorType();

        return $array;
    }
}
