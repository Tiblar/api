<?php
namespace App\Service\Billing;

use App\Entity\Billing\StripeCustomer;
use App\Entity\Billing\StripePaymentMethod;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Product;
use Symfony\Component\HttpFoundation\Request;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Stripe\Subscription;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Security;

class Stripe {

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var StripeClient
     */
    private $stripe;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var string
     */
    private $webhookSecret;

    public function __construct(
        EntityManagerInterface $em,
        Security $security,
        string $secretKey, string $webhookSecret
    )
    {
        $this->em = $em;
        $this->security = $security;
        $this->stripe = new StripeClient($secretKey);
        $this->webhookSecret = $webhookSecret;
    }

    public function createProduct(string $productId, string $title, string $description): Product
    {
        return $this->stripe->products->create([
            'name' => $title,
            'description' => $description,
            'metadata' => [
                'product_id' => $productId,
            ]
        ]);
    }

    public function createPrice(string $stripeProductId, string $currency, float $price, string $title, $recurring = [])
    {
        return $this->stripe->prices->create([
            'product' => $stripeProductId,
            'nickname' => $title,
            'unit_amount' => \bcmul($price, 100),
            'currency' => $currency,
            'recurring' => $recurring
        ]);
    }

    public function getPaymentMethod(?string $paymentMethodId): ?StripePaymentMethod
    {
        if(is_null($paymentMethodId)){
            return null;
        }

        $paymentMethod = $this->em->getRepository(StripePaymentMethod::class)->findOneBy([
            'userId' => $this->security->getUser()->getId(),
            'stripePaymentMethodId' => $paymentMethodId,
        ]);

        if($paymentMethod instanceof StripePaymentMethod){
            return $paymentMethod;
        }

        try {
            $result = $this->stripe->paymentMethods->retrieve($paymentMethodId);

            $customer = $this->getCustomer();

            if(!$customer instanceof StripeCustomer){
                return null;
            }

            $result->attach([
                'customer' => $this->getCustomer()->getStripeCustomerId(),
            ]);

            $paymentMethod = new StripePaymentMethod();
            $paymentMethod->setUserId($this->security->getUser()->getId());
            $paymentMethod->setName($result->billing_details->name);
            $paymentMethod->setEmail($result->billing_details->email);
            $paymentMethod->setStripePaymentId($result->id);
            $paymentMethod->setBrand($result->card->brand);
            $paymentMethod->setLastFour($result->card->last4);
            $paymentMethod->setActive(true);

            $this->em->persist($paymentMethod);
            $this->em->flush();

            if(!$customer->getDefaultPaymentMethod() instanceof StripePaymentMethod){
                $this->stripe->customers->update($customer->getStripeCustomerId(), [
                    'invoice_settings' => [
                        'default_payment_method' => $paymentMethod->getStripePaymentMethodId(),
                    ],
                ]);
            }

            return $paymentMethod;

        }catch (ApiErrorException $e) {
            return null;
        }
    }

    public function getCustomer(): ?StripeCustomer
    {
        $customer = $this->em->getRepository(StripeCustomer::class)->findOneBy([
            'userId' => $this->security->getUser()->getId(),
        ]);

        if($customer instanceof StripeCustomer){
            return $customer;
        }

        try {
            $result = $this->stripe->customers->create([
                'metadata' => [
                    'user_id' => $this->security->getUser()->getId(),
                ]
            ]);

            $customer = new StripeCustomer();
            $customer->setUserId($this->security->getUser()->getId());
            $customer->setStripeCustomerId($result->id);

            $this->em->persist($customer);
            $this->em->flush();

            return $customer;

        }catch (ApiErrorException $e) {
            return null;
        }
    }

    /**
     * @param string $orderId
     * @param string $customerId
     * @param string $paymentMethodId
     * @param array $items
     * @return \Stripe\Subscription|null
     */
    public function billQuantity(string $orderId, string $customerId, string $paymentMethodId, array $items): ?Subscription
    {
        try{
            return $this->stripe->subscriptions->create([
                'customer' => $customerId,
                'default_payment_method' => $paymentMethodId,
                'items' => $items,
                'metadata' => [
                    'order_id' => $orderId,
                ]
            ]);
        }catch (ApiErrorException $e){
            return null;
        }
    }

    public function cancelSubscription(string $subscriptionId): ?Subscription
    {
        try{
            return $this->stripe->subscriptions->cancel($subscriptionId);
        }catch (ApiErrorException $e){
            return null;
        }
    }

    public function parseWebhookEvent(Request $request): \Stripe\Event
    {
        $signature = $request->headers->get('Stripe-Signature');

        try {
            return \Stripe\Webhook::constructEvent(
                $request->getContent(false),
                $signature,
                $this->webhookSecret
            );
        } catch (\UnexpectedValueException $e) {
            throw new BadRequestHttpException('Invalid Stripe payload');
        } catch (SignatureVerificationException $e) {
            throw new BadRequestHttpException('Invalid Stripe signature');
        }
    }
}