<?php

namespace App\Entity\Billing;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="billing_invoice", indexes={
 *     @ORM\Index(name="id_idx", columns={"id"}),
 *     @ORM\Index(name="user_id_idx", columns={"id", "buyer_id", "seller_id"}),
 *     @ORM\Index(name="order_id_idx", columns={"order_invoices_id"}),
 *     @ORM\Index(name="status_idx", columns={"event", "payment_status"}),
 * })
 */
class Invoice
{
    static $INVOICE_EVENT_RECURRING_START = "INVOICE_EVENT_START";
    static $INVOICE_EVENT_RECURRING_UPGRADE = "INVOICE_EVENT_UPGRADE";
    static $INVOICE_EVENT_RECURRING_DOWNGRADE = "INVOICE_EVENT_DOWNGRADE";
    static $INVOICE_EVENT_RECURRING = "INVOICE_EVENT_RECURRING";

    // One time payment
    static $INVOICE_EVENT_ONCE = "INVOICE_EVENT_ONCE";

    static $INVOICE_STATUS_PAID = "INVOICE_STATUS_PAID";
    static $INVOICE_STATUS_DECLINED = "INVOICE_STATUS_DECLINED";
    static $INVOICE_STATUS_PENDING = "INVOICE_STATUS_PENDING";
    static $INVOICE_STATUS_DELETED = "INVOICE_STATUS_DELETED";
    static $INVOICE_STATUS_REFUNDED = "INVOICE_STATUS_REFUNDED";
    static $INVOICE_STATUS_FRAUD = "INVOICE_STATUS_FRAUD";
    static $INVOICE_STATUS_OTHER = "INVOICE_STATUS_OTHER";
    static $INVOICE_STATUS_EXPIRED = "INVOICE_STATUS_EXPIRED";

    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $txId;

    /**
     * @ORM\Column(type="string")
     */
    private $buyerId;

    /**
     * @ORM\Column(type="string")
     */
    private $sellerId;

    /**
     * Order
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Billing\Order", inversedBy="invoices")
     * @ORM\JoinColumn(name="order_invoices_id", referencedColumnName="id")
     */
    private $order;

    /**
     * Event of invoice
     *
     * @ORM\Column(type="string")
     */
    private $event;

    /**
     * Payment status
     *
     * @ORM\Column(type="string")
     */
    private $paymentStatus;

    /**
     * Payment method for invoice
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Billing\PaymentMethod", fetch="EAGER")
     * @ORM\JoinColumn(name="payment_method_id", referencedColumnName="id")
     */
    protected $paymentMethod;

    /**
     * Payment type of method
     *
     * @ORM\Column(type="string")
     */
    private $currency;

    /**
     * Payment type of method
     *
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expireTimestamp;

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
    public function getTxId(): string
    {
        return $this->txId;
    }

    /**
     * @param string $txId
     */
    public function setTxId(string $txId): void
    {
        $this->txId = $txId;
    }

    /**
     * @return string
     */
    public function getBuyerId(): string
    {
        return $this->buyerId;
    }

    /**
     * @param string $buyerId
     */
    public function setBuyerId(string $buyerId): void
    {
        $this->buyerId = $buyerId;
    }

    /**
     * @return string
     */
    public function getSellerId(): string
    {
        return $this->sellerId;
    }

    /**
     * @param string $sellerId
     */
    public function setSellerId(string $sellerId): void
    {
        $this->sellerId = $sellerId;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->order->getId();
    }

    /**
     * @param Order $order
     */
    public function setOrder(Order $order): void
    {
        $this->order = $order;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @throws \Exception
     */
    public function setCurrency(string $currency): void
    {
        if(
        in_array($currency, [
            Product::$CUR_USD,
        ])
        ){
            $this->currency = $currency;
        }else{
            throw new \Exception("Invalid currency type");
        }
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @param string $event
     * @throws \Exception
     */
    public function setEvent(string $event): void
    {
        if(
        in_array($event, [
            self::$INVOICE_EVENT_ONCE,
            self::$INVOICE_EVENT_RECURRING,
            self::$INVOICE_EVENT_RECURRING_START,
            self::$INVOICE_EVENT_RECURRING_UPGRADE,
            self::$INVOICE_EVENT_RECURRING_DOWNGRADE
        ])
        ){
            $this->event = $event;
        }else{
            throw new \Exception("Invalid invoice event type");
        }
    }

    /**
     * @return string
     */
    public function getPaymentStatus(): string
    {
        return $this->paymentStatus;
    }

    /**
     * @param string $paymentStatus
     * @throws \Exception
     */
    public function setPaymentStatus(string $paymentStatus): void
    {
        if(
        in_array($paymentStatus, [
            self::$INVOICE_STATUS_PAID,
            self::$INVOICE_STATUS_DECLINED,
            self::$INVOICE_STATUS_PENDING,
            self::$INVOICE_STATUS_DELETED,
            self::$INVOICE_STATUS_REFUNDED,
            self::$INVOICE_STATUS_FRAUD,
            self::$INVOICE_STATUS_OTHER,
            self::$INVOICE_STATUS_EXPIRED,
        ])
        ){
            $this->paymentStatus = $paymentStatus;
        }else{
            throw new \Exception("Invalid payment status");
        }
    }

    /**
     * @return null|PaymentMethod
     */
    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    /**
     * @param null|PaymentMethod $paymentMethod
     */
    public function setPaymentMethod(?PaymentMethod $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return null|\DateTime
     */
    public function getExpireTimestamp(): ?\DateTime
    {
        return $this->expireTimestamp;
    }

    /**
     * @param null|\DateTime $expireTimestamp
     */
    public function setExpireTimestamp(?\DateTime $expireTimestamp): void
    {
        $this->expireTimestamp = $expireTimestamp;
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
        $expireTimestamp = null;
        if($this->getExpireTimestamp() instanceof \DateTime){
            $expireTimestamp = $this->getExpireTimestamp()->format('c');
        }

        $paymentMethod = $this->getPaymentMethod();
        if($paymentMethod instanceof PaymentMethod){
            $paymentMethod = $paymentMethod->toArray();
        }

        return [
            'id' => $this->getId(),
            'tx_id' => $this->getTxId(),
            'buyer_id' => $this->getBuyerId(),
            'seller_id' => $this->getSellerId(),
            'event' => $this->getEvent(),
            'payment_status' => $this->getPaymentStatus(),
            'payment_method' => $paymentMethod,
            'expire_timestamp' => $expireTimestamp,
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}