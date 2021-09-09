<?php

namespace App\Controller\Actions\Market\Order;

use App\Controller\ApiController;
use App\Entity\Billing\Order;
use App\Service\Billing\GetBilling;
use App\Service\Billing\Retrieve\Fetch\Orders;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GetController extends ApiController
{
    /**
     * @Route("/market/order/{orderId}", name="market_get_order", methods={"GET"})
     */
    public function order(Request $request, Orders $orders, $orderId)
    {
        $em = $this->getDoctrine()->getManager();

        $order = $em->getRepository(Order::class)->findOneBy([
            'id' => $orderId,
            'buyerId' => $this->getUser()->getId(),
        ]);

        if(!$order instanceof Order){
            $order = $em->getRepository(Order::class)->findOneBy([
                'id' => $orderId,
                'sellerId' => $this->getUser()->getId(),
            ]);
        }

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

        return $this->respond([
            'order' => $orders->single($order->getId())
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