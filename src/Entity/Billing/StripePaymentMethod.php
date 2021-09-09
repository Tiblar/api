<?php

namespace App\Entity\Billing;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="billing_stripe_payment_method", indexes={
 *     @ORM\Index(name="id_idx", columns={"id"}),
 *     @ORM\Index(name="user_id_idx", columns={"user_id", "active"}),
 *     @ORM\Index(name="stripe_pm_idx", columns={"stripe_payment_method_id"}),
 * })
 */
class StripePaymentMethod
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
     * @ORM\Column(type="string", nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $stripePaymentMethodId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $brand;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $lastFour;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active = true;

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
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getStripePaymentMethodId(): string
    {
        return $this->stripePaymentMethodId;
    }

    /**
     * @param string $stripePaymentMethodId
     */
    public function setStripePaymentId(string $stripePaymentMethodId): void
    {
        $this->stripePaymentMethodId = $stripePaymentMethodId;
    }

    /**
     * @return string|null
     */
    public function getBrand(): string
    {
        return $this->brand;
    }

    /**
     * @param string|null $brand
     */
    public function setBrand(?string $brand): void
    {
        $this->brand = $brand;
    }

    /**
     * @return string|null
     */
    public function getLastFour(): string
    {
        return $this->lastFour;
    }

    /**
     * @param string|null $lastFour
     */
    public function setLastFour(?string $lastFour): void
    {
        $this->lastFour = $lastFour;
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
            'email' => $this->getEmail(),
            'name' => $this->getName(),
            'brand' => $this->getBrand(),
            'last_four' => $this->getLastFour(),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}