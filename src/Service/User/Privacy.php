<?php
namespace App\Service\User;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\User\Addons\Privacy as PrivacyEntity;

class Privacy
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Security
     */
    private $security;

    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    /**
     * @param string $userId profile id
     * @return PrivacyEntity|null
     */
    public function get($userId): ?PrivacyEntity
    {
        $privacy = $this->em->getRepository(PrivacyEntity::class)
            ->findOneBy([
                'userId' => $userId,
            ]);

        if(!$privacy INSTANCEOF PrivacyEntity){
            return null;
        }

        return $privacy;
    }
}
