<?php
namespace App\Structure\Billing;

use App\Entity\Billing\BillingAttribute;

class SanitizedBillingAttribute extends BillingAttribute
{
    public function __construct(array $arr)
    {
        parent::__construct();

        if(isset($arr['id'])){
            $this->setId($arr['id']);
        }

        if(isset($arr['productAttribute'])){
            $sanitizedAttribute = new SanitizedProductAttribute($arr['productAttribute']);
            $this->setProductAttribute($sanitizedAttribute);
        }

        if(isset($arr['quantity'])){
            $this->setQuantity($arr['quantity']);
        }

        if(isset($arr['timestamp'])){
            $this->setTimestamp($arr['timestamp']);
        }
    }

    public function setSanitizedProductAttribute(SanitizedProductAttribute $attribute): void
    {
        $this->productAttribute = $attribute;
    }

    public function getSanitizedProductAttribute(): SanitizedProductAttribute
    {
        return $this->productAttribute;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'attribute' => $this->getSanitizedProductAttribute()->toArray(),
            'quantity' => $this->getQuantity(),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}