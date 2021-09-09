<?php
namespace App\Service\Billing\Retrieve;

use App\Entity\Billing\Order;
use App\Entity\User\User;
use App\Service\Generator\Snowflake;
use App\Service\Billing\Retrieve\AddonsBuilder;
use App\Structure\Billing\SanitizedBillingAttribute;
use App\Structure\Billing\SanitizedInvoice;
use App\Structure\Billing\SanitizedOrder;
use App\Structure\Billing\SanitizedProduct;
use Doctrine\ORM\EntityManagerInterface;

class Formatter
{

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var AddonsBuilder
     */
    private $addonsBuilder;

    public function __construct(EntityManagerInterface $em, AddonsBuilder $addonsBuilder)
    {
        $this->em = $em;
        $this->addonsBuilder = $addonsBuilder;
    }

    public function orders(array $orderIds, array $addons = []): array
    {
        $orders = isset($addons['orders']) ? $addons['orders'] : null;
        $users = isset($addons['users']) ? $addons['users'] : null;

        if(is_null($orders)){
            $orders = $this->addonsBuilder->getOrders($orderIds);
        }

        if(is_null($users)){
            $users = $this->em->getRepository(User::class)
                ->findSanitizedUsers($this->addonsBuilder->getUsers($orders));
        }

        $sanitized = [];
        foreach($orders as $order){
            $sanitizedOrder = new SanitizedOrder($order);

            $sanitizedProduct = new SanitizedProduct($order['product']);

            foreach($users as $user){
                if(isset($order['product']['user']) && $order['product']['user']['id'] === $user->getId()){
                    $sanitizedProduct->setSanitizedUser($user);
                    break;
                }
            }

            $sanitizedOrder->setSanitizedProduct($sanitizedProduct);

            foreach($users as $user){
                if($order['sellerId'] === $user->getId()){
                    $sanitizedOrder->setSeller($user);
                }

                if($order['buyerId'] === $user->getId()){
                    $sanitizedOrder->setBuyer($user);
                }
            }

            foreach($order['invoices'] as $invoice){
                $sanitizedInvoice = new SanitizedInvoice($invoice);

                foreach($users as $user){
                    if($invoice['sellerId'] === $user->getId()){
                        $sanitizedInvoice->setSeller($user);
                    }

                    if($invoice['buyerId'] === $user->getId()){
                        $sanitizedInvoice->setBuyer($user);
                    }
                }

                $sanitizedOrder->addSanitizedInvoice($sanitizedInvoice);
            }

            $sanitized[] = $sanitizedOrder;
        }

        return $sanitized;
    }

    public static function formatPrice($key, $value)
    {
        if(!is_array($value)){
            if($key === "price"){
                return (float) number_format($value, 2, '.', '');
            }else{
                return $value;
            }
        }

        foreach($value as $k => &$v){
            $v = self::formatPrice($k, $v);
        }

        return $value;
    }

    public static function formatUsers($users, $orders): array
    {
        foreach($orders as &$order){
            if($order instanceof Order){
                $order = $order->toArray();
            }else{
                continue;
            }

            foreach($users as $user){
                if($order['seller_id'] === $user->getId()){
                    $order['seller'] = $user->toArray();
                    unset($order['seller_id']);
                    break;
                }
            }

            foreach($users as $user){
                if($order['buyer_id'] === $user->getId()){
                    $order['buyer'] = $user->toArray();
                    unset($order['buyer_id']);
                    break;
                }
            }

            if($order['seller_id'] === Snowflake::createSystemSnowflake()){
                $order['seller'] = null;
                unset($order['seller_id']);
            }

            $invoices = array_column($order, 'invoices');

            foreach($invoices as $key => $invoice){
                foreach($users as $user){
                    if($invoice['seller_id'] === $user->getId()){
                        $order[$key]['seller'] = $user->toArray();
                        unset($order[$key]['seller_id']);
                        break;
                    }
                }

                foreach($users as $user){
                    if($invoice['buyer_id'] === $user->getId()){
                        $invoice['buyer'] = $user->toArray();
                        unset($order[$key]['buyer_id']);
                        break;
                    }
                }

                if($invoice['seller_id'] === Snowflake::createSystemSnowflake()){
                    $order[$key]['seller'] = null;
                    unset($order[$key]['buyer_id']);
                }
            }
        }

        return $orders;
    }
}
