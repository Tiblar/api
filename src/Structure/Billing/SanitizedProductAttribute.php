<?php
namespace App\Structure\Billing;

use App\Entity\Billing\ProductAttribute;

class SanitizedProductAttribute extends ProductAttribute
{
    public function __construct(array $arr)
    {
        parent::__construct();

        if(isset($arr['id'])){
            $this->setId($arr['id']);
        }

        if(isset($arr['minQuantity'])){
            $this->setMinQuantity($arr['minQuantity']);
        }

        if(isset($arr['maxQuantity'])){
            $this->setMaxQuantity($arr['maxQuantity']);
        }

        if(isset($arr['title'])){
            $this->setTitle($arr['title']);
        }

        if(isset($arr['description'])){
            $this->setDescription($arr['description']);
        }

        if(isset($arr['value'])){
            $this->setValue($arr['value']);
        }

        if(isset($arr['price'])){
            $this->setPrice($arr['price']);
        }

        if(isset($arr['timestamp'])){
            $this->setTimestamp($arr['timestamp']);
        }
    }
}