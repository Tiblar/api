<?php

namespace App\Controller\Actions\Market\Webhook;

use App\Controller\ApiController;
use App\Entity\Billing\Order;
use App\Entity\Billing\PaymentMethod;
use App\Service\Billing\OrderManager;
use App\Service\Billing\Stripe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends ApiController
{
    /**
     * @Route("/market/webhook/stripe", name="market_webhook_stripe", methods={"POST"})
     */
    public function stripe(Request $request, Stripe $stripe, OrderManager $orderManager)
    {
        $em = $this->getDoctrine()->getManager();

        $event = $stripe->parseWebhookEvent($request);

        if(!in_array($event->type, ['invoice.paid', 'customer.subscription.deleted'])){
            return $this->respond([
                'event' => 'Acknowledging'
            ], null, 200);
        }

        $cancelled = false;
        if($event->type === 'customer.subscription.deleted'){
            $cancelled = true;
        }

        $orderId = null;
        $price = null;
        $currency = null;
        $txId = null;

        if(!$cancelled){
            $lines = $event->data->object->lines->data;
            foreach($lines as $line){
                if(isset($line->metadata->order_id) && !is_null($line->metadata->order_id)){
                    $orderId = $line->metadata->order_id;
                    break;
                }
            }

            $price = $event->data->object->amount_paid;

            if(is_numeric($price)){
                $price = (float) number_format($price/100, 2, '.', '');
            }

            $currency = strtoupper($event->data->object->currency);
            $txId = $event->data->object->id;
        }

        if($cancelled){
            $orderId = $event->data->object->metadata->order_id;
        }

        $order = $em->getRepository(Order::class)->findOneBy([
            'id' => $orderId
        ]);

        if(!($order instanceof Order)){
            return $this->respondWithErrors([
                'custom' => 'Order not found.'
            ], null, 404);
        }

        if($event->type === 'customer.subscription.deleted'){
            if($orderManager->subscribeCancel($order->getId())){
                return $this->respond([]);
            }

            return $this->respondWithErrors([
                'subscribe' => 'Something went wrong.'
            ], 500);
        }

        if($event->type === 'invoice.paid'){
            if($currency !== $order->getCurrency()){
                return $this->respondWithErrors([
                    'currency' => 'Wrong currency (' . $order->getCurrency() . ' needed).'
                ], null, 400);
            }

            if($price !== $order->getPrice()){
                return $this->respondWithErrors([
                    'amount_paid' => 'Wrong payment amount (' . $order->getPrice() . $order->getCurrency() . ' needed).'
                ], null, 400);
            }

            if(
                $orderManager->subscribePayment(
                    $order,
                    PaymentMethod::$TYPE_STRIPE,
                    $txId,
                    $currency,
                    $price
                )
            ) {
                return $this->respond([]);
            }

            return $this->respondWithErrors([
                'subscribe' => 'Something went wrong.'
            ], 500);
        }

        return $this->respond([
            'event' => $event->type
        ]);
    }
}