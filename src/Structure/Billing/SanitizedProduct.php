<?php
namespace App\Structure\Billing;

use App\Entity\Billing\BillingAttribute;
use App\Entity\Billing\Product;
use App\Structure\User\SanitizedUser;

class SanitizedProduct extends Product
{
    public function __construct(array $arr)
    {
        parent::__construct();

        $this->attributes = [];

        if(isset($arr['id'])){
            $this->setId($arr['id']);
        }

        if(isset($arr['title'])){
            $this->setTitle($arr['title']);
        }

        if(isset($arr['description'])){
            $this->setDescription($arr['description']);
        }

        if(isset($arr['currency'])){
            $this->setCurrency($arr['currency']);
        }

        if(isset($arr['price'])){
            $this->setPrice($arr['price']);
        }

        if(isset($arr['subscriptionFrequency']) && $arr['subscriptionFrequency']){
            if(is_array($arr['subscriptionFrequency'])){
                foreach($arr['subscriptionFrequency'] as $value){
                    if(is_string($value)){
                        $this->addSubscriptionFrequency($value);
                    }else{
                        throw new \Exception("Subscription frequency not a string.");
                    }
                }
            }
        }

        if(isset($arr['attributes']) && is_array($arr['attributes'])){
            foreach($arr['attributes'] as $attribute){
                $sanitizedAttribute = new SanitizedProductAttribute($attribute);
                $this->addSanitizedAttribute($sanitizedAttribute);
            }
        }

        if(isset($arr['annualDiscount'])){
            $this->setAnnualDiscount($arr['annualDiscount']);
        }

        if(isset($arr['userLimit'])){
            $this->setUserLimit($arr['userLimit']);
        }

        if(isset($arr['shipping'])){
            $this->setShipping($arr['shipping']);
        }

        if(isset($arr['published'])){
            $this->setPublished($arr['published']);
        }

        if(isset($arr['unpublishedTimestamp'])){
            $this->setUnpublishedTimestamp($arr['unpublishedTimestamp']);
        }

        if(isset($arr['timestamp'])){
            $this->setTimestamp($arr['timestamp']);
        }
    }

    /**
     * @return SanitizedUser
     */
    public function getSanitizedUser(): ?SanitizedUser
    {
        return $this->user;
    }

    /**
     * @param SanitizedUser $user
     */
    public function setSanitizedUser(SanitizedUser $user): void
    {
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function getSanitizedAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param SanitizedProductAttribute $attribute
     */
    public function addSanitizedAttribute(SanitizedProductAttribute $attribute): void
    {
        $this->attributes[] = $attribute;
    }

    public function toArray(): array
    {
        $user = null;
        if($this->getSanitizedUser() instanceof SanitizedUser){
            $user = $this->getSanitizedUser()->toArray();
        }

        $unpublishedTimestamp = null;
        if($this->getUnpublishedTimestamp() instanceof \DateTime){
            $unpublishedTimestamp = $this->getUnpublishedTimestamp()->format('c');
        }

        $attributes = $this->getSanitizedAttributes();
        $attributesArray = [];
        foreach($attributes as $attribute){
            $attributesArray[] = $attribute->toArray();
        }

        return [
            'id' => $this->getId(),
            'user' => $user,
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'currency' => $this->getCurrency(),
            'price' => $this->getPrice(),
            'subscript_frequency' => $this->getSubscriptionFrequency(),
            'annual_discount' => $this->getAnnualDiscount(),
            'user_limit' => $this->getUserLimit(),
            'attributes' => $attributesArray,
            'shipping' => $this->getShipping(),
            'published' => $this->isPublished(),
            'unpublished_timestamp' => $unpublishedTimestamp,
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}