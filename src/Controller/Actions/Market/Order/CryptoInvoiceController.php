<?php

namespace App\Controller\Actions\Market\Order;

use App\Controller\ApiController;
use App\Entity\Billing\Invoice;
use App\Entity\Billing\Order;
use App\Entity\Billing\PaymentMethod;
use App\Service\Billing\Retrieve\Fetch\Orders;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CryptoInvoiceController extends ApiController
{
    /**
     * @Route("/market/order/{orderId}/crypto-invoice", name="market_order_crypto_invoice", methods={"POST"})
     */
    public function invoice(Request $request, Orders $orders, $orderId)
    {
        $em = $this->getDoctrine()->getManager();

        $order = $em->getRepository(Order::class)->findOneBy([
            'id' => $orderId,
            'buyerId' => $this->getUser()->getId(),
        ]);

        if(!$order instanceof Order){
            return $this->respondWithErrors([
                'id' => 'Order not found.'
            ], null, 404);
        }

        $invoice = $em->createQueryBuilder()
            ->select('i')
            ->from('App:Billing\Invoice', 'i')
            ->where('i.buyerId = :userId')
            ->andWhere('i.order = :orderId')
            ->setParameter('userId', $this->getUser()->getId())
            ->setParameter('orderId', $order->getId())
            ->orderBy('i.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if(!($invoice instanceof Invoice)){
            return $this->respondWithErrors([
                'id' => 'Order needs previous invoices.'
            ], null, 404);
        }

        if(!($invoice->getPaymentMethod() instanceof PaymentMethod)){
            return $this->respondWithErrors([
                'id' => 'Order needs previous invoices.'
            ], null, 404);
        }

        if(!in_array($invoice->getPaymentMethod()->getType(), PaymentMethod::cryptoTypes())){
            return $this->respondWithErrors([
                'id' => 'Order must be a crypto type.'
            ], null, 404);
        }

        $method = $invoice->getPaymentMethod()->getType();

        $timestamp = new \DateTime();

        $invoice = $em->createQueryBuilder()
            ->select('i')
            ->from('App:Billing\Invoice', 'i')
            ->where('i.buyerId = :userId')
            ->andWhere('i.order = :orderId')
            ->andWhere('i.expireTimestamp > :timestamp')
            ->setParameter('userId', $this->getUser()->getId())
            ->setParameter('orderId', $order->getId())
            ->setParameter('timestamp', $timestamp)
            ->orderBy('i.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if($invoice instanceof Invoice){
            return $this->respond([
                'order' => $orders->single($order->getId())
            ]);
        }

        return $this->forward('App\Controller\Actions\Market\Purchase\CryptoController::crypto', [
            'request' => $request,
            'order' => $order,
            'frequency' => $order->getFrequency(),
            'method' => $method,
        ]);
    }
}