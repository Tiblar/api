<?php

namespace App\Controller\Actions\Market\Purchase;

use App\Controller\ApiController;
use App\Entity\Billing\CryptoPaymentMethod;
use App\Entity\Billing\Invoice;
use App\Entity\Billing\Order;
use App\Entity\Billing\PaymentMethod;
use App\Entity\Billing\Product;
use App\Service\Billing\CoinPayments;
use App\Service\Billing\Retrieve\Fetch\Orders;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CryptoController extends ApiController
{
    public function crypto(
        Request $request, Order $order, string $frequency, string $method,
        CoinPayments $coinPayments, Orders $orders
    ) {
        $em = $this->getDoctrine()->getManager();

        $timestamp = new \DateTime();

        $invoice = $em->createQueryBuilder()
            ->select('i')
            ->from('App:Billing\Invoice', 'i')
            ->leftJoin('i.paymentMethod', 'p')
            ->leftJoin('i.order', 'o')
            ->where('i.buyerId = :userId')
            ->andWhere('i.expireTimestamp > :timestamp')
            ->andWhere('o.product = :productId')
            ->andWhere('o.active = false')
            ->andWhere('o.frequency = :frequency')
            ->andWhere('p.type = :paymentMethod')
            ->andWhere('p.cancelled = false')
            ->setParameter('userId', $this->getUser()->getId())
            ->setParameter('paymentMethod', $method)
            ->setParameter('productId', $order->getProduct()->getId())
            ->setParameter('frequency', $frequency)
            ->setParameter('timestamp', $timestamp)
            ->orderBy('i.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if($invoice instanceof Invoice){
            return $this->respond([
                'order' => $orders->single($invoice->getOrderId())
            ]);
        }

        $invoice = new Invoice();
        $em->persist($invoice);

        $transaction = $coinPayments->createTransaction($invoice, $order, $method);

        if(is_null($transaction)){
            return $this->respondWithErrors([
                'crypto' => "Something went wrong generating a crypto address. Contact support.",
            ]);
        }

        $expireTimestamp = new \DateTime();
        $expireTimestamp->modify("+" . $transaction['timeout'] . " seconds");

        $invoice->setExpireTimestamp($expireTimestamp);

        $cryptoPaymentMethod = new CryptoPaymentMethod();
        $cryptoPaymentMethod->setUserId($this->getUser()->getId());
        $cryptoPaymentMethod->setType($method);
        $cryptoPaymentMethod->setAddress($transaction['address']);
        $cryptoPaymentMethod->setAmount((float) $transaction['amount']);
        $cryptoPaymentMethod->setDestTag(isset($transaction['dest_tag']) ? $transaction['dest_tag'] : null);
        $em->persist($cryptoPaymentMethod);

        $paymentMethod = new PaymentMethod();
        $paymentMethod->setUserId($order->getBuyerId());
        $paymentMethod->setOrderId($order->getId());
        $paymentMethod->setRecurring(false);
        $paymentMethod->setCancelled(false);
        $paymentMethod->setCryptoPaymentMethod($cryptoPaymentMethod);
        $paymentMethod->setType($method);

        if($frequency === Product::$DUR_ANNUALLY) {
            $paymentMethod->setRecurring(true);
        }

        if($frequency === Product::$DUR_MONTHLY) {
            $paymentMethod->setRecurring(true);
        }

        $em->persist($paymentMethod);

        $invoice->setTxId($transaction['txn_id']);
        $invoice->setSellerId($order->getSellerId());
        $invoice->setBuyerId($order->getBuyerId());
        $invoice->setOrder($order);
        $invoice->setCurrency($order->getCurrency());
        $invoice->setPrice($order->getPrice());
        $invoice->setEvent(Invoice::$INVOICE_EVENT_RECURRING);
        $invoice->setPaymentStatus(Invoice::$INVOICE_STATUS_PENDING);
        $invoice->setPaymentMethod($paymentMethod);

        $em->persist($invoice);

        $order->addInvoice($invoice);

        $em->flush();

        return $this->respond([
            'order' => $orders->single($order->getId())
        ]);
    }
}