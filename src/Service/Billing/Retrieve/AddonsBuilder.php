<?php
namespace App\Service\Billing\Retrieve;

use Doctrine\ORM\EntityManagerInterface;

class AddonsBuilder
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getUsers($value, $key = null, $users = [])
    {
        if(!is_array($value) || $key === "user"){
            if($key === "sellerId" || $key === "buyerId"){
                $users[] = $value;
            }

            if(is_array($value) && $key === "user" && isset($value['id'])){
                $users[] = $value['id'];
            }

            return array_unique($users);
        }

        foreach($value as $k => &$v){
            $users = $this->getUsers($v, $k, $users);
        }

        return $users;
    }

    public function getOrders(array $orderIds): array
    {
        return $this->em->createQueryBuilder()
            ->select('o', 'p', 'pa', 'pu', 'i', 'ipm', 'c', 'a', 'ap')
            ->from('App:Billing\Order', 'o')
            ->leftJoin('o.product', 'p')
            ->leftJoin('p.attributes', 'pa')
            ->leftJoin('p.user', 'pu')
            ->leftJoin('o.invoices', 'i')
            ->leftJoin('i.paymentMethod', 'ipm')
            ->leftJoin('ipm.cryptoPaymentMethod', 'c')
            ->leftJoin('o.attributes', 'a')
            ->leftJoin('a.productAttribute', 'ap')
            ->where('o.id IN (:ids)')
            ->orderBy('o.id', 'DESC')
            ->orderBy('i.id', 'DESC')
            ->setParameter('ids', $orderIds)
            ->getQuery()
            ->getArrayResult();
    }
}