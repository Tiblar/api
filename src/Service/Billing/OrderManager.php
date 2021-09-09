<?php
namespace App\Service\Billing;

use App\Entity\Billing\Invoice;
use App\Entity\Billing\Order;
use App\Entity\Billing\PaymentMethod;
use App\Entity\Billing\Product;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class OrderManager
{
    /**
     * @var EntityManagerInterface $em
     */
    private $em;

    /**
     * @var string
     */
    private $boostProductId;

    /**
     * @var string
     */
    private $storageAttributeId;

    private $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, string $boostProductId, string $storageAttributeId)
    {
        $this->em = $em;
        $this->boostProductId = $boostProductId;
        $this->storageAttributeId = $storageAttributeId;
        $this->logger = $logger;
    }

    public function subscribePayment(Order $order, string $paymentMethod, string $txId, string $currency, float $price): bool
    {
        if(!in_array($paymentMethod, [
            PaymentMethod::$TYPE_PAYPAL, PaymentMethod::$TYPE_STRIPE,
            PaymentMethod::$TYPE_BITCOIN, PaymentMethod::$TYPE_MONERO
        ])){
            throw new \Exception("Invalid payment method for subscribe payment.");
        }

        $event = Invoice::$INVOICE_EVENT_RECURRING;

        $order->setActive(true);

        $method = null;
        if(count($order->getInvoices())){
            $checkMethod = $order->getInvoices()[0]->getPaymentMethod();
            if($checkMethod instanceof PaymentMethod && $checkMethod->getType() === $paymentMethod){
                $method = $checkMethod;
            }
        }

        if($method === null){
            $event = Invoice::$INVOICE_EVENT_RECURRING_START;

            $method = new PaymentMethod();
            $method->setUserId($order->getBuyerId());
            $method->setOrderId($order->getId());
            $method->setRecurring(true);
            $method->setCancelled(false);
            $method->setType($paymentMethod);

            $this->em->persist($method);
        }

        $invoice = new Invoice();
        $invoice->setTxId($txId);
        $invoice->setSellerId($order->getSellerId());
        $invoice->setBuyerId($order->getBuyerId());
        $invoice->setOrder($order);
        $invoice->setCurrency($currency);
        $invoice->setPrice($price);
        $invoice->setEvent($event);
        $invoice->setPaymentStatus(Invoice::$INVOICE_STATUS_PAID);
        $invoice->setPaymentMethod($method);
        $this->em->persist($invoice);

        $order->addInvoice($invoice);

        $expireTimestamp = $order->getExpireTimestamp();

        if(is_null($expireTimestamp)){
            $expireTimestamp = new \DateTime();
        }else{
            $expireTimestamp = new \DateTime($expireTimestamp);
        }

        if($order->getFrequency() === Product::$DUR_MONTHLY){
            $expireTimestamp->modify("+1 month");
            $order->setExpireTimestamp($expireTimestamp);
        }

        if($order->getFrequency() === Product::$DUR_ANNUALLY){
            $expireTimestamp->modify("+1 year");
            $order->setExpireTimestamp($expireTimestamp);
        }

        $this->em->flush();

        if($this->boostProductId === $order->getProduct()->getId()){
            $this->boostUser($order);
        }

        return true;
    }

    public function subscribeCancel(string $orderId): bool
    {

        $this->em->getConnection()->beginTransaction();

        try {
            $qb = $this->em->createQueryBuilder();
            $qb->update('App:Billing\Order', 'o')
                ->where('o.id = :orderId')
                ->set('o.active', $qb->expr()->literal(false))
                ->setParameter('orderId', $orderId)
                ->getQuery()
                ->execute();

            $qb = $this->em->createQueryBuilder();
            $qb->update('App:Billing\PaymentMethod', 'p')
                ->where('p.orderId = :orderId')
                ->set('p.cancelled', $qb->expr()->literal(true))
                ->setParameter('orderId', $orderId)
                ->getQuery()
                ->execute();

            $timestamp = new \DateTime();

            $qb = $this->em->createQueryBuilder();
            $qb->update('App:Billing\Invoice', 'i')
                ->where('i.order = :orderId')
                ->andWhere('i.expireTimestamp > :timestamp')
                ->set('i.expireTimestamp', 'NULL')
                ->set('i.paymentStatus', $qb->expr()->literal(Invoice::$INVOICE_STATUS_EXPIRED))
                ->setParameter('orderId', $orderId)
                ->setParameter('timestamp', $timestamp)
                ->getQuery()
                ->execute();

            $this->em->getConnection()->commit();

            return true;
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            $this->logger->error($e->getMessage());
            return false;
        }
    }

    public function boostUser(Order $order)
    {
        $user = $this->em->getRepository(User::class)->findOneBy([
            'id' => $order->getBuyerId()
        ]);

        if($user instanceof User){
            $user->setBoosted(true);

            $limit = 0;

            foreach($order->getAttributes() as $attribute){
                if($this->storageAttributeId === $attribute->getProductAttribute()->getId()){
                    $limit += ($attribute->getProductAttribute()->getValue() * $attribute->getQuantity());
                }
            }

            if($user->getStorageLimit() < $limit){
                $user->setStorageLimit($limit);
            }

            $this->em->flush();
        }
    }
}