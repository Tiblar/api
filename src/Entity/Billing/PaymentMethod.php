<?php

namespace App\Entity\Billing;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="billing_payment_method", indexes={
 *     @ORM\Index(name="id_idx", columns={"id"}),
 *     @ORM\Index(name="user_id_idx", columns={"id", "user_id"}),
 *     @ORM\Index(name="order_id_idx", columns={"order_id", "recurring", "cancelled"}),
 *     @ORM\Index(name="crypto_payment_method_idx", columns={"crypto_payment_method"}),
 * })
 */
class PaymentMethod
{
    static $TYPE_STRIPE = "STRIPE";
    static $TYPE_PAYPAL = "PAYPAL";
    static $TYPE_BITCOIN = "BITCOIN";
    static $TYPE_MONERO = "MONERO";

    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private $id;

    /**
     * User who is paying
     *
     * @ORM\Column(type="string")
     */
    private $userId;

    /**
     * Id of the order
     *
     * @ORM\Column(type="string")
     */
    private $orderId;

    /**
     * Payment type of method
     *
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * If method is recurring
     *
     * @ORM\Column(type="boolean")
     */
    private $recurring;

    /**
     * If method is cancelled
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $cancelled;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Billing\CryptoPaymentMethod", fetch="EAGER")
     * @ORM\JoinColumn(name="crypto_payment_method", referencedColumnName="id")
     */
    private $cryptoPaymentMethod;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp = null;

    public function __construct()
    {
        $this->timestamp = new \DateTime();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     */
    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     */
    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @throws \Exception
     */
    public function setType(string $type): void
    {
        if(
        in_array($type, [
            self::$TYPE_STRIPE, self::$TYPE_PAYPAL, self::$TYPE_BITCOIN, self::$TYPE_MONERO
        ])
        ){
            $this->type = $type;
        }else{
            throw new \Exception("Invalid payment method type");
        }
    }

    /**
     * @return bool
     */
    public function isRecurring(): bool
    {
        return $this->recurring;
    }

    /**
     * @param bool $recurring
     */
    public function setRecurring(bool $recurring): void
    {
        $this->recurring = $recurring;
    }

    /**
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->cancelled;
    }

    /**
     * @param bool $cancelled
     */
    public function setCancelled(bool $cancelled): void
    {
        $this->cancelled = $cancelled;
    }

    /**
     * @return CryptoPaymentMethod|null
     */
    public function getCryptoPaymentMethod(): ?CryptoPaymentMethod
    {
        return $this->cryptoPaymentMethod;
    }

    /**
     * @param CryptoPaymentMethod|null $cryptoPaymentMethod
     */
    public function setCryptoPaymentMethod(?CryptoPaymentMethod $cryptoPaymentMethod): void
    {
        $this->cryptoPaymentMethod = $cryptoPaymentMethod;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    /**
     * @param \DateTime $timestamp
     */
    public function setTimestamp(\DateTime $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function toArray(): array
    {
        $crypto = null;
        if($this->getCryptoPaymentMethod() instanceof CryptoPaymentMethod){
            $crypto = $this->getCryptoPaymentMethod()->toArray();
        }

        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'recurring' => $this->isRecurring(),
            'crypto' => $crypto,
            'cancelled' => $this->isCancelled(),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}