<?php

namespace App\Controller\Actions\Market\Order;

use App\Controller\ApiController;
use App\Entity\Billing\Order;
use App\Service\Billing\GetBilling;
use App\Service\Billing\OrderManager;
use App\Service\Billing\Retrieve\Fetch\Orders;
use App\Service\Billing\Stripe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CancelController extends ApiController
{
    /**
     * @Route("/market/order/{orderId}", name="market_cancel_order", methods={"DELETE"})
     */
    public function order(Request $request, GetBilling $billing, Stripe $stripe, OrderManager $orderManager, $orderId)
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

        $userId = $this->getUser()->getId();
        if($order->getBuyerId() !== $userId && $order->getSellerId() !== $userId){
            return $this->respondWithErrors([
                'id' => 'Order not found.'
            ], null, 404);
        }

        $stripeSubscriptionId = $order->getStripeSubscriptionId();

        if(!is_null($stripeSubscriptionId)){
            $stripe->cancelSubscription($stripeSubscriptionId);
        }

        $orderManager->subscribeCancel($order->getId());

        $em->clear();

        $order = $em->getRepository(Order::class)->findOneBy([
            'id' => $orderId,
        ]);

        if(!$order instanceof Order){
            return $this->respondWithErrors([
                'id' => 'Order not found.'
            ], null, 404);
        }

        return $this->respond([
            'order' => $billing->orderToArray($order)
        ]);
    }

    /**
     * @Route("/market/orders/outgoing", name="market_get_orders_outgoing", methods={"GET"})
     */
    public function ordersOutgoing(Request $request, Orders $fetch)
    {
        $offset = $request->query->get('offset');
        $offset = intval($offset);

        $orders = $fetch->outgoing($offset);

        return $this->respond([
            'orders' => $orders
        ]);
    }
}