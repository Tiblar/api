<?php

namespace App\Entity\Billing;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="billing_billing_attribute", indexes={
 *     @ORM\Index(name="id_idx", columns={"id"}),
 *     @ORM\Index(name="user_id_idx", columns={"id", "seller_id", "buyer_id"}),
 *     @ORM\Index(name="order_id_idx", columns={"order_attributes_id"}),
 *     @ORM\Index(name="product_attribute_id_idx", columns={"product_attribute_id"}),
 * })
 */
class BillingAttribute
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private $id;

    /**
     * User who owns the product
     *
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Billing\Order", inversedBy="attributes")
     * @ORM\JoinColumn(name="order_attributes_id", referencedColumnName="id")
     */
    private $order;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Billing\ProductAttribute", fetch="EAGER")
     * @ORM\JoinColumn(name="product_attribute_id", referencedColumnName="id")
     */
    protected $productAttribute;

    /**
     * Number of product attribute
     *
     * @ORM\Column(type="decimal")
     */
    protected $quantity;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $timestamp = null;

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
     * @return ProductAttribute
     */
    public function getProductAttribute(): ProductAttribute
    {
        return $this->productAttribute;
    }

    /**
     * @param ProductAttribute $attribute
     */
    public function setProductAttribute(ProductAttribute $attribute): void
    {
        $this->productAttribute = $attribute;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
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
        return [
            'id' => $this->getId(),
            'attribute' => $this->getProductAttribute()->toArray(),
            'quantity' => $this->getQuantity(),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}