<?php
namespace App\Service\Billing;

use App\Entity\Billing\Order;
use App\Entity\Billing\Product;
use App\Entity\User\Addons\Connection;
use App\Service\Billing\Retrieve\Formatter;
use Doctrine\ORM\EntityManagerInterface;

class GetBilling
{
    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    /**
     * @var string
     */
    private $paypal;

    public function __construct(EntityManagerInterface $em, string $paypal)
    {
        $this->em = $em;
        $this->paypal = $paypal;
    }

    /**
     * @param Order $order
     * @return array
     */
    public function orderToArray(Order $order): array
    {
        $array = $order->toArray();
        $array['product'] = $this->productToArray($order->getProduct());

        $array['price'] = number_format($array['price'], 2, '.', '');

        return Formatter::formatPrice(null, $array);
    }

    /**
     * @param Product $product
     * @return array
     */
    public function productToArray(Product $product): array
    {
        $userId = $product->getUser() ? $product->getUser()->getId() : null;

        $connections = $this->em->createQueryBuilder()
            ->select('c.service, c.account, c.link')
            ->from('App:User\Addons\Connection', 'c')
            ->where('c.userId = :userId')
            ->andWhere('c.service = :paypal')
            ->setParameter('userId', $userId)
            ->setParameter('paypal', Connection::$SERVICE_PAYPAL)
            ->orderBy('CHAR_LENGTH(c.id)', 'DESC')
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getArrayResult();

        $paypal = null;
        if(is_null($userId)){
            $paypal = $this->paypal;
        }

        foreach($connections as $connection){
            if(filter_var($connection['account'], FILTER_VALIDATE_EMAIL)){
                $paypal = $connections['account'];
            }
        }

        $array = $product->toArray();
        $array['seller_paypal'] = $paypal;

        return Formatter::formatPrice(null, $array);
    }
}
