<?php

namespace App\Controller\Actions\Market\Order;

use App\Controller\ApiController;
use App\Entity\Billing\Order;
use App\Service\Billing\Retrieve\Fetch\Orders;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GetBoostController extends ApiController
{
    /**
     * @Route("/market/order/boost", name="market_get_boost_order", methods={"GET"})
     */
    public function boost(Request $request, Orders $orders)
    {
        $em = $this->getDoctrine()->getManager();

        $timestamp = new \DateTime();

        $order = $em->createQueryBuilder()
            ->select('o')
            ->from('App:Billing\Order', 'o')
            ->where('o.product = :productId')
            ->andWhere('o.buyerId = :userId')
            ->andWhere('o.expireTimestamp > :timestamp')
            ->andWhere('o.expireTimestamp IS NOT NULL')
            ->setParameter('productId', $this->getParameter('boost_product_id'))
            ->setParameter('userId', $this->getUser()->getId())
            ->setParameter('timestamp', $timestamp)
            ->orderBy('o.expireTimestamp', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if(!$order instanceof Order){
            return $this->respondWithErrors([
                'id' => 'Order not found.'
            ], null, 404);
        }

        return $this->respond([
            'order' => $orders->single($order->getId())
        ]);
    }
}