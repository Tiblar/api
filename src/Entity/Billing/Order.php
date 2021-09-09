<?php

namespace App\Entity\Billing;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="billing_order", indexes={
 *     @ORM\Index(name="id_idx", columns={"id"}),
 *     @ORM\Index(name="user_id_idx", columns={"id", "buyer_id", "seller_id"}),
 *     @ORM\Index(name="order_id_idx", columns={"id", "recurring", "active"})
 * })
 */
class Order
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $buyerId;

    /**
     * @ORM\Column(type="string")
     */
    private $sellerId;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Billing\Product", fetch="EAGER")
     * @ORM\JoinColumn(name="order_product_id", referencedColumnName="id")
     */
    protected $product;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Billing\Invoice", mappedBy="order", fetch="EAGER", cascade={"remove", "persist"}, orphanRemoval=true
     * )
     * @ORM\JoinColumn(name="order_invoice_id", referencedColumnName="id")
     */
    protected $invoices;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Billing\BillingAttribute", mappedBy="order", fetch="EAGER", cascade={"remove", "persist"}, orphanRemoval=true
     * )
     * @ORM\JoinColumn(name="product_attribute_id", referencedColumnName="id")
     */
    protected $attributes;

    /**
     * Frequency of billing
     * null if once
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $frequency;

    /**
     * @ORM\Column(type="boolean")
     */
    private $recurring;

    /**
     * If subscription is active
     *
     * @ORM\Column(type="boolean")
     */
    private $active = false;

    /**
     * After expire timestamp lapses,
     * can mark this as true for internal systems
     *
     * @ORM\Column(type="boolean")
     */
    private $expired = false;

    /**
     * Frequency of billing
     * null if once
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $stripeSubscriptionId;

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
        $this->invoices = new ArrayCollection();
        $this->attributes = new ArrayCollection();
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
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }

    /**
     * @return ?array
     */
    public function getInvoices(): array
    {
        return $this->invoices->toArray();
    }

    /**
     * @param Invoice $invoice
     */
    public function addInvoice(Invoice $invoice): void
    {
        $this->invoices->add($invoice);
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes->toArray();
    }

    /**
     * @param BillingAttribute $attribute
     */
    public function addAttribute(BillingAttribute $attribute): void
    {
        $this->attributes->add($attribute);
    }

    /**
     * @return string|null
     */
    public function getFrequency(): ?string
    {
        return $this->frequency;
    }

    /**
     * @param string|null $frequency
     * @throws \Exception
     */
    public function setFrequency(?string $frequency): void
    {
        if(!in_array($frequency, [Product::$DUR_MONTHLY, Product::$DUR_ANNUALLY, null])){
            throw new \Exception("Invalid subscription frequency type");
        }

        $this->frequency = $frequency;
    }

    /**
     * @return bool
     */
    public function getRecurring(): bool
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
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expired;
    }

    /**
     * @param bool $expired
     */
    public function setExpired(bool $expired): void
    {
        $this->expired = $expired;
    }

    /**
     * @return string|null
     */
    public function getStripeSubscriptionId(): ?string
    {
        return $this->stripeSubscriptionId;
    }

    /**
     * @param string|null $stripeSubscriptionId
     */
    public function setStripeSubscriptionId(?string $stripeSubscriptionId): void
    {
        $this->stripeSubscriptionId = $stripeSubscriptionId;
    }

    /**
     * @return \DateTime|null
     */
    public function getExpireTimestamp(): ?\DateTime
    {
        return $this->expireTimestamp;
    }

    /**
     * @param \DateTime|null $expireTimestamp
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

    /**
     * @return array
     */
    public function toArray(): array
    {
        $product = $this->getProduct();

        $invoices = [];
        foreach($this->getInvoices() as $invoice){
            $invoices[] = $invoice->toArray();
        }

        $attributes = [];
        foreach($this->getAttributes() as $attribute){
            $attributes[] = $attribute->toArray();
        }

        $expireTimestamp = $this->getExpireTimestamp()
            ? $this->getExpireTimestamp()->format('c') : null;

        return [
            'id' => $this->getId(),
            'buyer_id' => $this->getBuyerId(),
            'seller_id' => $this->getSellerId(),
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