<?php

namespace App\Controller\Actions\Market\IPN;

use App\Controller\ApiController;
use App\Entity\Billing\CryptoPaymentMethod;
use App\Entity\Billing\Invoice;
use App\Entity\Billing\Order;
use App\Entity\Billing\PaymentMethod;
use App\Entity\Billing\Product;
use App\Service\Billing\CoinPayments;
use App\Service\Billing\OrderManager;
use App\Service\Billing\PayPal;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CryptoController extends ApiController
{
    /**
     * @Route("/market/ipn/coin-payments", name="market_ipn_coin_payments", methods={"POST"})
     */
    public function coinPayments(Request $request, CoinPayments $coinPayments, OrderManager $orderManager, LoggerInterface $logger)
    {
        $em = $this->getDoctrine()->getManager();

        $logger->error($request->getContent(false));

        $check = $coinPayments->validateIPN($request);

        if($check === false){
             return $this->respondWithErrors([], "Invalid signature.", 403);
        }

        $invoiceId = $request->request->get('invoice');

        $invoice = $em->getRepository(Invoice::class)->findOneBy([
           'id' => $invoiceId,
        ]);

        if(!($invoice instanceof Invoice)){
            return $this->respondWithErrors([
                'invoice' => 'Invoice not found.'
            ], null, 404);
        }

        $order = $em->getRepository(Order::class)->findOneBy([
            'id' => $invoice->getOrderId(),
        ]);

        if(!($order instanceof Order)){
            return $this->respondWithErrors([
                'order' => 'Order not found.'
            ], null, 404);
        }

        $status = intval($request->request->get('status'));

        if($status === 0 || $request->request->get('ipn_type') !== "api"){
            return $this->respond([
                'status' => 'Acknowledging'
            ], null, 200);
        }

        if($status <= 0){
            $invoice->setPaymentStatus(Invoice::$INVOICE_STATUS_EXPIRED);
            $em->flush();

            return $this->respond([
                'status' => 'Acknowledging'
            ], null, 200);
        }

        $currency = strtoupper($request->request->get('currency1'));

        $price = (float) $request->request->get('amount1');
        $priceCrypto = (float) $request->request->get('amount2');

        if($price !== $order->getPrice()){
            return $this->respondWithErrors([
                'amount1' => 'Wrong payment amount (' . $order->getPrice() . $order->getCurrency() . ' needed).'
            ], null, 400);
        }

        if($currency !== $order->getCurrency()){
            return $this->respondWithErrors([
                'currency1' => 'Wrong currency (' . $order->getCurrency() . ' needed).'
            ], null, 400);
        }

        $cryptoPaymentMethod = $invoice->getPaymentMethod()->getCryptoPaymentMethod();

        if(!($cryptoPaymentMethod instanceof CryptoPaymentMethod)){
            return $this->respondWithErrors([
                'crypto' => 'Crypto payment method not found.'
            ], null, 404);
        }

        $cryptoPaymentMethod->setConfirmations(intval($request->request->get('received_confirms')));

        $received = (float) $request->request->get('received_amount');

        if($received >= $priceCrypto && $cryptoPaymentMethod->getConfirmations() > 1){
            $invoice->setPaymentStatus(Invoice::$INVOICE_STATUS_PAID);

            $order->setActive(true);

            $expireTimestamp = $order->getExpireTimestamp();

            if(is_null($expireTimestamp)){
                $expireTimestamp = new \DateTime();
            }else{
                $expireTimestamp = new \DateTime($expireTimestamp->format("c"));
            }

            if($order->getFrequency() === Product::$DUR_MONTHLY){
                $expireTimestamp->modify("+1 month");
                $order->setExpireTimestamp($expireTimestamp);
            }

            if($order->getFrequency() === Product::$DUR_ANNUALLY){
                $expireTimestamp->modify("+1 year");
                $order->setExpireTimestamp($expireTimestamp);
            }

            $invoice->setExpireTimestamp(null);

            $em->flush();


            $orderManager->boostUser($order);
            $logger->error("here");

        }

        return $this->respond([]);
    }
}