<?php
namespace App\Structure\Billing;

use App\Entity\Billing\CryptoPaymentMethod;

class SanitizedCrypto extends CryptoPaymentMethod
{
    public function __construct(array $arr)
    {
        parent::__construct();

        if(isset($arr['id'])){
            $this->setId($arr['id']);
        }

        if(isset($arr['type'])){
            $this->setType($arr['type']);
        }

        if(isset($arr['address'])){
            $this->setAddress($arr['address']);
        }

        if(isset($arr['dest_tag'])){
            $this->setDestTag($arr['dest_tag']);
        }

        if(isset($arr['amount'])){
            $this->setAmount($arr['amount']);
        }

        if(isset($arr['timestamp'])){
            $this->setTimestamp($arr['timestamp']);
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'address' => $this->getAddress(),
            'dest_tag' => $this->getDestTag(),
            'amount' => $this->getAmount(),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}