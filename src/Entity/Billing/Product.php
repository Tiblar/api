<?php

namespace App\Entity\Billing;

use App\Entity\User\User;
use App\Structure\User\SanitizedUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="billing_product", indexes={
 *     @ORM\Index(name="id_idx", columns={"id"}),
 *     @ORM\Index(name="user_idx", columns={"id", "product_user_id"}),
 *     @ORM\Index(name="stripe_product_id_idx", columns={"stripe_product_id"}),
 * })
 */
class Product
{
    static $CUR_USD = "USD";

    static $DUR_MONTHLY = "MONTHLY";
    static $DUR_ANNUALLY = "ANNUALLY";

    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", fetch="EAGER")
     * @ORM\JoinColumn(name="product_user_id", referencedColumnName="id")
     */
    protected $user;

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
     * @ORM\Column(type="string")
     */
    private $currency;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private $price;

    /**
     * Length of subscription
     * Null if not a subscription
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $subscriptionFrequency;

    /**
     * If allow annual subscription, apply this discount %
     * Null if not applicable
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $annualDiscount;

    /**
     * Total number of active orders allowed
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $userLimit;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Billing\ProductAttribute", mappedBy="product", fetch="EAGER", cascade={"remove", "persist"}, orphanRemoval=true
     * )
     * @ORM\JoinColumn(name="product_attribute_id", referencedColumnName="id")
     */
    protected $attributes;

    /**
     * If shipping needed
     *
     * @ORM\Column(type="boolean")
     */
    private $shipping;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $stripeProductId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $stripePriceId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $stripePriceAnnualDiscountId;

    /**
     * If shipping needed
     *
     * @ORM\Column(type="boolean")
     */
    private $published = true;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $unpublishedTimestamp;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp = null;

    public function __construct()
    {
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
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
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
            self::$CUR_USD,
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
     * @return ?array
     */
    public function getSubscriptionFrequency(): ?array
    {
        return json_decode($this->subscriptionFrequency);
    }

    /**
     * @param string $frequency
     * @throws \Exception
     */
    public function addSubscriptionFrequency(string $frequency): void
    {
        if(!in_array($frequency, [self::$DUR_MONTHLY, self::$DUR_ANNUALLY])){
            throw new \Exception("Invalid subscription frequency type");
        }

        $frequencies = json_decode($this->subscriptionFrequency);

        $frequencies[] = $frequency;

        $this->subscriptionFrequency = json_encode($frequencies);

    }

    /**
     * @return int|null
     */
    public function getAnnualDiscount(): ?int
    {
        return $this->annualDiscount;
    }

    /**
     * @param int|null $annualDiscount
     */
    public function setAnnualDiscount(?int $annualDiscount): void
    {
        $this->annualDiscount = $annualDiscount;
    }

    /**
     * @return int|null
     */
    public function getUserLimit(): ?int
    {
        return $this->userLimit;
    }

    /**
     * @param mixed $userLimit
     */
    public function setUserLimit($userLimit): void
    {
        $this->userLimit = $userLimit;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes->toArray();
    }

    /**
     * @param ProductAttribute $attribute
     */
    public function addAttribute(ProductAttribute $attribute): void
    {
        $this->attributes->add($attribute);
    }

    /**
     * @param ProductAttribute $attribute
     * @return bool
     */
    public function removeAttribute(ProductAttribute $attribute): bool
    {
        return $this->attributes->removeElement($attribute);
    }

    /**
     * @return bool
     */
    public function getShipping(): bool
    {
        return $this->shipping;
    }

    /**
     * @param bool $shipping
     */
    public function setShipping(bool $shipping): void
    {
        $this->shipping = $shipping;
    }

    /**
     * @return string|null
     */
    public function getStripeProductId(): ?string
    {
        return $this->stripeProductId;
    }

    /**
     * @param string|null $stripeProductId
     */
    public function setStripeProductId(?string $stripeProductId): void
    {
        $this->stripeProductId = $stripeProductId;
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
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->published;
    }

    /**
     * @param bool $published
     */
    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }

    /**
     * @return \DateTime|null
     */
    public function getUnpublishedTimestamp(): ?\DateTime
    {
        return $this->unpublishedTimestamp;
    }

    /**
     * @param \DateTime|null $unpublishedTimestamp
     */
    public function setUnpublishedTimestamp(?\DateTime $unpublishedTimestamp): void
    {
        $this->unpublishedTimestamp = $unpublishedTimestamp;
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
        $user = null;
        if($this->getUser() instanceof User){
            $sanitizedUser = new SanitizedUser($user);
            $user = $sanitizedUser->toArray();
        }

        $unpublishedTimestamp = null;
        if($this->getUnpublishedTimestamp() instanceof \DateTime){
            $unpublishedTimestamp = $this->getUnpublishedTimestamp()->format('c');
        }

        $attributes = $this->getAttributes();
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