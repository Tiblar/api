<?php

namespace App\Controller\Actions\Market\IPN;

use App\Controller\ApiController;
use App\Entity\Billing\Order;
use App\Entity\Billing\PaymentMethod;
use App\Service\Billing\OrderManager;
use App\Service\Billing\PayPal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PayPalController extends ApiController
{
    /**
     * @Route("/market/ipn/paypal", name="market_ipn_paypal", methods={"POST"})
     */
    public function paypal(Request $request, PayPal $paypal, OrderManager $orderManager)
    {
        $em = $this->getDoctrine()->getManager();

        $raw = $request->getContent();
        $check = $paypal->verify($raw);

        if($check === false){
            return $this->respondWithErrors([], "Invalid signature.", 403);
        }

        $txType = $request->request->get('txn_type');

        if(!in_array($txType, ['subscr_payment', 'subscr_cancel', 'subscr_eot'])){
            return $this->respond([
                'txn_type' => 'Acknowledging'
            ], null, 200);
        }

        $cancel = false;
        if(in_array($txType, ['subscr_cancel', 'subscr_eot'])){
            $cancel = true;
        }

        $currency = strtoupper($request->request->get('mc_currency'));
        $price = (float) $request->request->get('mc_gross');
        $orderId = $request->request->get('custom');

        $txId = $request->request->get('txn_id');

        $order = $em->getRepository(Order::class)->findOneBy([
            'id' => $orderId
        ]);

        if(!($order instanceof Order)){
            return $this->respondWithErrors([
                'custom' => 'Order not found.'
            ], null, 404);
        }

        if($price !== $order->getPrice() && !$cancel){
            return $this->respondWithErrors([
                'mc_gross' => 'Wrong payment amount (' . $order->getPrice() . $order->getCurrency() . ' needed).'
            ], null, 400);
        }

        if($currency !== $order->getCurrency() && !$cancel){
            return $this->respondWithErrors([
                'mc_currency' => 'Wrong currency (' . $order->getCurrency() . ' needed).'
            ], null, 400);
        }

        if($cancel){
            if($orderManager->subscribeCancel($order->getId())){
                return $this->respond([]);
            }

            return $this->respondWithErrors([
                'subscribe' => 'Something went wrong.'
            ], 500);
        }

        if($txType === 'subscr_payment'){
            if($orderManager->subscribePayment($order, PaymentMethod::$TYPE_PAYPAL, $txId, $currency, $price)) {
                return $this->respond([]);
            }

            return $this->respondWithErrors([
                'subscribe' => 'Something went wrong.'
            ], 500);
        }

        return $this->respond([]);
    }
}