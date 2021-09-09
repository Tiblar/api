<?php

namespace App\Controller\Actions\Market\Purchase;

use App\Controller\ApiController;
use App\Entity\Billing\Order;
use App\Service\Billing\GetBilling;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PayPalController extends ApiController
{
    public function paypal(Request $request, Order $order, string $frequency, GetBilling $billing)
    {
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        return $this->respond([
            'order' => $billing->orderToArray($order),
        ]);
    }
}