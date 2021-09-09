<?php

namespace App\Entity\Billing;

use App\Entity\User\Addons\ConfirmEmail;
use App\Entity\User\Addons\Privacy;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="billing_product_attribute", indexes={
 *     @ORM\Index(name="id_idx", columns={"id"}),
 *     @ORM\Index(name="user_id_idx", columns={"id", "user_id"})
 * })
 */
class ProductAttribute
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
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Billing\Product", inversedBy="attributes")
     * @ORM\JoinColumn(name="product_attribute_id", referencedColumnName="id")
     */
    private $product;

    /**
     * Minimum quantity
     *
     * @ORM\Column(type="integer")
     */
    private $minQuantity;

    /**
     * Maximum quantity
     *
     * @ORM\Column(type="integer")
     */
    private $maxQuantity;

    /**
     * Title for attribute
     *
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * Description of what the attribute is
     *
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * Any custom value associated with the attribute
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $value;

    /**
     * Price per attribute unit
     * For instance 2x attribute would be 2x price
     *
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private $price;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $stripePriceId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $stripePriceAnnualDiscountId;

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
     * @return int
     */
    public function getMinQuantity(): int
    {
        return $this->minQuantity;
    }

    /**
     * @param int $minQuantity
     */
    public function setMinQuantity(int $minQuantity): void
    {
        $this->minQuantity = $minQuantity;
    }

    /**
     * @return int
     */
    public function getMaxQuantity(): int
    {
        return $this->maxQuantity;
    }

    /**
     * @param int $maxQuantity
     */
    public function setMaxQuantity(int $maxQuantity): void
    {
        $this->maxQuantity = $maxQuantity;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
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
     * @return string|null
     */
    public function getStripePriceId(): ?string
    {
        return $this->stripePriceId;
    }

    /**
     * @param string|null $stripePriceId
     */
    public function setStripePriceId(?string $stripePriceId): void
    {
        $this->stripePriceId = $stripePriceId;
    }

    /**
     * @return string|null
     */
    public function getStripePriceAnnualDiscountId(): ?string
    {
        return $this->stripePriceAnnualDiscountId;
    }

    /**
     * @param string|null $stripePriceAnnualDiscountId
     */
    public function setStripePriceAnnualDiscountId(?string $stripePriceAnnualDiscountId): void
    {
        $this->stripePriceAnnualDiscountId = $stripePriceAnnualDiscountId;
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
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'price' => $this->getPrice(),
            'value' => $this->getValue(),
            'min_quantity' => $this->getMinQuantity(),
            'max_quantity' => $this->getMaxQuantity(),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}