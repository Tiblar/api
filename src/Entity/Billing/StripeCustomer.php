<?php

namespace App\Entity\Billing;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="billing_stripe_customer", indexes={
 *     @ORM\Index(name="id_idx", columns={"id"}),
 *     @ORM\Index(name="user_id_idx", columns={"user_id"}),
 *     @ORM\Index(name="stripe_customer_idx", columns={"stripe_customer_id"}),
 *     @ORM\Index(name="default_payment_method_idx", columns={"default_payment_method_id"}),
 * })
 */
class StripeCustomer
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $userId;

    /**
     * @ORM\Column(type="string")
     */
    private $stripeCustomerId;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Billing\StripePaymentMethod", fetch="EAGER")
     * @ORM\JoinColumn(name="default_payment_method_id", referencedColumnName="id")
     */
    private $defaultPaymentMethod;

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
    public function getStripeCustomerId(): string
    {
        return $this->stripeCustomerId;
    }

    /**
     * @param string $stripeCustomerId
     */
    public function setStripeCustomerId(string $stripeCustomerId): void
    {
        $this->stripeCustomerId = $stripeCustomerId;
    }

    /**
     * @return StripePaymentMethod|null
     */
    public function getDefaultPaymentMethod(): ?StripePaymentMethod
    {
        return $this->defaultPaymentMethod;
    }

    /**
     * @param StripePaymentMethod|null $defaultPaymentMethod
     */
    public function setDefaultPaymentMethod(?StripePaymentMethod $defaultPaymentMethod): void
    {
        $this->defaultPaymentMethod = $defaultPaymentMethod;
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
        $method = null;
        if($this->getDefaultPaymentMethod() instanceof StripePaymentMethod){
            $method = $this->getDefaultPaymentMethod()->toArray();
        }

        return [
            'id' => $this->getId(),
            'default_payment_method' => $method,
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}