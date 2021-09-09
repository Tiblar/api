<?php
namespace App\Structure\Billing;

use App\Entity\Billing\Invoice;
use App\Structure\User\SanitizedUser;

class SanitizedInvoice extends Invoice
{
    private $buyer;

    private $seller;

    public function __construct(array $arr)
    {
        parent::__construct();

        if(isset($arr['id'])){
            $this->setId($arr['id']);
        }

        if(isset($arr['txId'])){
            $this->setTxId($arr['txId']);
        }

        if(isset($arr['event'])){
            $this->setEvent($arr['event']);
        }

        if(isset($arr['paymentStatus'])){
            $this->setPaymentStatus($arr['paymentStatus']);
        }

        if(isset($arr['paymentMethod'])){
            $sanitizedPaymentMethod = new SanitizedPaymentMethod($arr['paymentMethod']);
            $this->setSanitizedPaymentMethod($sanitizedPaymentMethod);
        }

        if(isset($arr['currency'])){
            $this->setCurrency($arr['currency']);
        }

        if(isset($arr['price'])){
            $this->setPrice($arr['price']);
        }

        if(isset($arr['expireTimestamp'])){
            $this->setExpireTimestamp($arr['expireTimestamp']);
        }

        if(isset($arr['timestamp'])){
            $this->setTimestamp($arr['timestamp']);
        }
    }

    /**
     * @return SanitizedUser|null
     */
    public function getBuyer(): ?SanitizedUser
    {
        return $this->buyer;
    }

    /**
     * @param SanitizedUser $buyer
     */
    public function setBuyer(SanitizedUser $buyer): void
    {
        $this->buyer = $buyer;
    }

    /**
     * @return SanitizedUser|null
     */
    public function getSeller(): ?SanitizedUser
    {
        return $this->seller;
    }

    /**
     * @param SanitizedUser $seller
     */
    public function setSeller(SanitizedUser $seller): void
    {
        $this->seller = $seller;
    }

    /**
     * @return null|SanitizedPaymentMethod
     */
    public function getSanitizedPaymentMethod(): ?SanitizedPaymentMethod
    {
        return $this->paymentMethod;
    }

    /**
     * @param null|SanitizedPaymentMethod $paymentMethod
     */
    public function setSanitizedPaymentMethod(?SanitizedPaymentMethod $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    public function toArray(): array
    {
        $expireTimestamp = null;
        if($this->getExpireTimestamp() instanceof \DateTime){
            $expireTimestamp = $this->getExpireTimestamp()->format('c');
        }

        $paymentMethod = $this->getPaymentMethod();
        if($paymentMethod instanceof SanitizedPaymentMethod){
            $paymentMethod = $paymentMethod->toArray();
        }

        $buyer = null;
        if($this->getBuyer() instanceof SanitizedUser){
            $buyer = $this->getBuyer()->toArray();
        }

        $seller = null;
        if($this->getSeller() instanceof SanitizedUser){
            $seller = $this->getSeller()->toArray();
        }

        return [
            'id' => $this->getId(),
            'tx_id' => $this->getTxId(),
            'buyer' => $buyer,
            'seller' => $seller,
            'event' => $this->getEvent(),
            'payment_status' => $this->getPaymentStatus(),
            'payment_method' => $paymentMethod,
            'currency' => $this->getCurrency(),
            'price' => $this->getPrice(),
            'expire_timestamp' => $expireTimestamp,
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}