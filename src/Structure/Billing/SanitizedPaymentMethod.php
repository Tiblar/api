<?php
namespace App\Structure\Billing;

use App\Entity\Billing\PaymentMethod;

class SanitizedPaymentMethod extends PaymentMethod
{
    /**
     * @var
     */
    private $crypto;

    public function __construct(array $arr)
    {
        parent::__construct();

        if(isset($arr['id'])){
            $this->setId($arr['id']);
        }

        if(isset($arr['type'])){
            $this->setType($arr['type']);
        }

        if(isset($arr['recurring'])){
            $this->setRecurring($arr['recurring']);
        }

        if(isset($arr['cancelled'])){
            $this->setCancelled($arr['cancelled']);
        }

        if(isset($arr['cryptoPaymentMethod']) && !is_null($arr['cryptoPaymentMethod'])){
            $crypto = new SanitizedCrypto($arr['cryptoPaymentMethod']);
            $this->setCrypto($crypto);
        }

        if(isset($arr['timestamp'])){
            $this->setTimestamp($arr['timestamp']);
        }
    }

    function getCrypto(): ?SanitizedCrypto
    {
        return $this->crypto;
    }

    public function setCrypto(SanitizedCrypto $crypto)
    {
        $this->crypto = $crypto;
    }

    public function toArray(): array
    {
        $crypto = null;

        if($this->getCrypto() instanceof SanitizedCrypto){
            $crypto = $this->getCrypto()->toArray();
        }

        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'recurring' => $this->isRecurring(),
            'cancelled' => $this->isCancelled(),
            'crypto' => $crypto,
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}