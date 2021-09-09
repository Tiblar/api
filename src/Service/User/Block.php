<?php
namespace App\Service\User;

use App\Structure\User\BlockStructure;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class Block
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
     * @param $userId
     * @return BlockStructure
     */
    public function get($userId): BlockStructure
    {
        $blocked = $this->em->createQueryBuilder()
            ->select('b.blockedId')
            ->from(\App\Entity\User\Addons\Block::class, 'b')
            ->where('(b.blockerId = :userId)')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();

        $blockers = $this->em->createQueryBuilder()
            ->select('b.blockerId')
            ->from(\App\Entity\User\Addons\Block::class, 'b')
            ->where('(b.blockedId = :userId)')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();

        return new BlockStructure(
            array_column($blocked, 'blockedId'),
            array_column($blockers, 'blockerId')
        );
    }

    public function relationship($userId)
    {
        if(!$this->security->isGranted("ROLE_USER") || $userId === $this->security->getUser()->getId()){
            return [
                'is_blocked' => false,
                'is_blocked_by' => false,
            ];
        }

        $follow = $this->get($userId);
        $id = $this->security->getToken()->getUser()->getId();

        $array = [
            'is_blocked' => false,
            'is_blocked_by' => false,
        ];

        if(in_array($id, $follow->getBlockers())){
            $array['is_blocked'] = true;
        }

        if(in_array($id, $follow->getBlocking())){
            $array['is_blocked_by'] = true;
        }

        return $array;
    }
}
