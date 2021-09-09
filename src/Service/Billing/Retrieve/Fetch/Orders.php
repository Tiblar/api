<?php
namespace App\Service\Billing\Retrieve\Fetch;

use App\Service\Billing\Retrieve\Formatter;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;

class Orders
{
    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var Formatter
     */
    private $formatter;

    public function __construct(EntityManagerInterface $em, Security $security, Formatter $formatter)
    {
        $this->em = $em;
        $this->security = $security;
        $this->formatter = $formatter;
    }

    /**
     * @param int $offset
     * @return array
     */
    public function outgoing(int $offset): array
    {
        $userId = $this->security->getUser()->getId();

        $qb = $this->em->createQueryBuilder()
            ->select('o.id')
            ->from('App:Billing\Order', 'o')
            ->where('o.buyerId = :buyerId');

        $qb->having(
            $qb->expr()->gt(
                "(" .
                $this->em
                    ->createQueryBuilder()
                    ->select('COUNT(i.id)')
                    ->from('App:Billing\Invoice', 'i')
                    ->where('i.order = o.id AND i.buyerId = :buyerId')
                    ->getDQL()
                . ")"
                ,
                0
            )
        );

        $ids = $qb->setParameter('buyerId', $userId)
            ->setMaxResults(10)
            ->setFirstResult($offset)
            ->orderBy('o.id', 'DESC')
            ->getQuery()
            ->getArrayResult();
        $ids = array_column($ids, 'id');

        $orders = $this->formatter->orders($ids);

        return array_map(function ($order) {
            return $order->toArray();
        },  $orders);
    }

    public function single(string $orderId): ?array
    {
        $orders = $this->formatter->orders([$orderId]);

        $array = array_map(function ($order) {
            return $order->toArray();
        },  $orders);

        if(count($array) > 0){
            return $array[0];
        }

        return null;
    }
}
