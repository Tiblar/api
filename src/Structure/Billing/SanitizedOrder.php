<?php
namespace App\Structure\Billing;

use App\Entity\Billing\BillingAttribute;
use App\Entity\Billing\Order;
use App\Structure\User\SanitizedUser;

class SanitizedOrder extends Order
{
    private $buyer;

    private $seller;

    public function __construct(array $arr)
    {
        parent::__construct();

        $this->attributes = [];
        $this->invoices = [];

        if(isset($arr['id'])){
            $this->setId($arr['id']);
        }

        if(isset($arr['currency'])){
            $this->setCurrency($arr['currency']);
        }

        if(isset($arr['price'])){
            $this->setPrice($arr['price']);
        }

        if(isset($arr['frequency'])){
            $this->setFrequency($arr['frequency']);
        }

        if(isset($arr['attributes']) && is_array($arr['attributes'])){
            foreach($arr['attributes'] as $attribute){
                $sanitizedAttribute = new SanitizedBillingAttribute($attribute);
                $this->addSanitizedAttribute($sanitizedAttribute);
            }
        }

        if(isset($arr['recurring'])){
            $this->setRecurring($arr['recurring']);
        }

        if(isset($arr['active'])){
            $this->setActive($arr['active']);
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
     * @return SanitizedProduct
     */
    public function getSanitizedProduct(): SanitizedProduct
    {
        return $this->product;
    }

    /**
     * @param SanitizedProduct $product
     */
    public function setSanitizedProduct(SanitizedProduct $product): void
    {
        $this->product = $product;
    }

    /**
     * @return ?array
     */
    public function getSanitizedInvoices(): array
    {
        return $this->invoices;
    }

    /**
     * @param SanitizedInvoice $invoice
     */
    public function addSanitizedInvoice(SanitizedInvoice $invoice): void
    {
        $this->invoices[] = $invoice;
    }

    /**
     * @return array
     */
    public function getSanitizedAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param BillingAttribute $attribute
     */
    public function addSanitizedAttribute(BillingAttribute $attribute): void
    {
        $this->attributes[] = $attribute;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $product = $this->getSanitizedProduct();

        $invoices = [];
        foreach($this->getSanitizedInvoices() as $invoice){
            $invoices[] = $invoice->toArray();
        }

        $attributes = [];
        foreach($this->getSanitizedAttributes() as $attribute){
            $attributes[] = $attribute->toArray();
        }

        $expireTimestamp = $this->getExpireTimestamp()
            ? $this->getExpireTimestamp()->format('c') : null;

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
            'buyer' => $buyer,
            'seller' => $seller,
            'product' => $product->toArray(),
            'invoices' => $invoices,
            'currency' => $this->getCurrency(),
            'price' => $this->getPrice(),
            'attributes' => $attributes,
            'frequency' => $this->getFrequency(),
            'recurring' => $this->getRecurring(),
            'active' => $this->isActive(),
            'expire_timestamp' => $expireTimestamp,
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}