<?php

namespace App\Controller\Actions\Market\Purchase;

use App\Controller\ApiController;
use App\Entity\Billing\Order;
use App\Entity\Billing\Product;
use App\Entity\Billing\StripeCustomer;
use App\Entity\Billing\StripePaymentMethod;
use App\Service\Billing\GetBilling;
use App\Service\Billing\Stripe;
use Stripe\Subscription;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends ApiController
{
    public function stripe(Request $request, Order $order, string $frequency, GetBilling $billing, Stripe $stripe)
    {
        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $stripePaymentId = $request->request->get('stripe_payment_method_id');

        $stripePayment = $stripe->getPaymentMethod($stripePaymentId);

        if(!$stripePayment instanceof StripePaymentMethod){
            return $this->respond([
                'stripe_payment_method_id' => 'Invalid stripe payment id.'
            ], null, 400);
        }

        $customer = $stripe->getCustomer();

        if(!$customer instanceof StripeCustomer){
            return $this->respond([
                'token' => 'Invalid token.'
            ], null, 400);
        }

        $prices = [];

        if($frequency === Product::$DUR_ANNUALLY){
            $prices[] = [
                'price' => $order->getProduct()->getStripePriceAnnualDiscountId(),
                'quantity' => 1,
            ];

            foreach($order->getAttributes() as $attribute){
                $prices[] = [
                    'price' => $attribute->getProductAttribute()->getStripePriceAnnualDiscountId(),
                    'quantity' => $attribute->getQuantity(),
                ];
            }
        }

        if($frequency === Product::$DUR_MONTHLY){
            $prices[] = [
                'price' => $order->getProduct()->getStripePriceId(),
                'quantity' => 1,
            ];

            foreach($order->getAttributes() as $attribute){
                $prices[] = [
                    'price' => $attribute->getProductAttribute()->getStripePriceId(),
                    'quantity' => $attribute->getQuantity(),
                ];
            }
        }

        if($order->getRecurring()){
            $result = $stripe->billQuantity(
                $order->getId(),
                $customer->getStripeCustomerId(),
                $stripePayment->getStripePaymentMethodId(),
                $prices
            );

            if(!$result instanceof Subscription){
                return $this->respond([
                    'payment_method' => 'A problem happened charging your card.'
                ], null, 400);
            }

            $order->setStripeSubscriptionId($result->id);
            $em->flush();
        }

        return $this->respond([
            'order' => $billing->orderToArray($order),
        ]);
    }
}