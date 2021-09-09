<?php

namespace App\Controller\Actions\Market\Purchase;

use App\Controller\ApiController;
use App\Entity\Billing\BillingAttribute;
use App\Entity\Billing\Order;
use App\Entity\Billing\PaymentMethod;
use App\Entity\Billing\Product;
use App\Service\Generator\Snowflake;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PurchaseController extends ApiController
{
    /**
     * @Route("/market/product/{productId}/purchase", name="market_purchase_product", methods={"POST"})
     */
    public function product(Request $request, $productId)
    {
        $method = $request->request->get('payment_method');

        $attributes = $request->request->get('attributes');
        $frequency = $request->request->get('frequency');

        $em = $this->getDoctrine()->getManager();

        $product = $em->getRepository(Product::class)->findOneBy([
            'id' => $productId,
        ]);

        if(
            !($product instanceof Product) || !$product->isPublished() ||
            (!is_null($product->getUserLimit()) && $product->getUserLimit() === 0)
        ){
            return $this->respondWithErrors([
                'id' => 'Order not found.'
            ], null, 404);
        }

        $validFrequency = null;
        foreach($product->getSubscriptionFrequency() as $value){
            if($frequency === $value){
                $validFrequency = $value;
            }
        }

        if(!is_null($product->getSubscriptionFrequency()) && is_null($validFrequency)){
            return $this->respondWithErrors([
                'frequency' => 'Invalid frequency.'
            ], null, 400);
        }

        $sellerId = $product->getUser() ? $product->getUser()->getId() : Snowflake::createSystemSnowflake();
        $isRecurring = (!is_null($product->getSubscriptionFrequency()) && !empty($product->getSubscriptionFrequency()));

        $order = new Order();
        $order->setBuyerId($this->getUser()->getId());
        $order->setSellerId($sellerId);
        $order->setProduct($product);
        $order->setRecurring($isRecurring);
        $order->setActive(false);
        $order->setFrequency($frequency);
        $order->setCurrency($product->getCurrency());
        $em->persist($order);

        $price = $product->getPrice();

        foreach($product->getAttributes() as $attribute){
            if(isset($attributes[$attribute->getId()])){
                $value = $attributes[$attribute->getId()];
                if($value >= $attribute->getMinQuantity() && $value <= $attribute->getMaxQuantity()){
                    $billingAttribute = new BillingAttribute();
                    $billingAttribute->setBuyerId($this->getUser()->getId());
                    $billingAttribute->setSellerId($sellerId);
                    $billingAttribute->setOrder($order);
                    $billingAttribute->setProductAttribute($attribute);
                    $billingAttribute->setQuantity($value);
                    $em->persist($billingAttribute);

                    $order->addAttribute($billingAttribute);

                    $price += ($billingAttribute->getQuantity() * $attribute->getPrice());
                }else{
                    return $this->respond([
                        'attribute' => 'You have an invalid amount of: ' . $attribute->getId() . '.'
                    ], null, 400);
                }
            }
        }

        if($frequency === Product::$DUR_ANNUALLY){
            $price *= 12;

            if($product->getAnnualDiscount()){
                $price *= (1 - ($product->getAnnualDiscount() / 100));
            }
        }

        $order->setPrice((float) number_format($price, 2, '.', ''));

        if($method === PaymentMethod::$TYPE_PAYPAL) {
            return $this->forward('App\Controller\Actions\Market\Purchase\PayPalController::paypal', [
                'request' => $request,
                'order' => $order,
                'frequency' => $frequency,
            ]);
        }

        if($method === PaymentMethod::$TYPE_STRIPE) {
            return $this->forward('App\Controller\Actions\Market\Purchase\StripeController::stripe', [
                'request' => $request,
                'order' => $order,
                'frequency' => $frequency,
            ]);
        }

        if(in_array($method, [PaymentMethod::$TYPE_BITCOIN, PaymentMethod::$TYPE_MONERO])) {
            return $this->forward('App\Controller\Actions\Market\Purchase\CryptoController::crypto', [
                'request' => $request,
                'order' => $order,
                'frequency' => $frequency,
                'method' => $method,
            ]);
        }

        return $this->respond([
            'method' => 'Invalid payment method.'
        ], null, 400);
    }
}