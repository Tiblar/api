<?php

namespace App\Entity\Billing;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="billing_crypto_payment_method", indexes={
 *     @ORM\Index(name="id_idx", columns={"id"}),
 *     @ORM\Index(name="user_id_idx", columns={"user_id"}),
 *     @ORM\Index(name="address_idx", columns={"address", "dest_tag"}),
 *     @ORM\Index(name="type_idx", columns={"type"}),
 * })
 */
class CryptoPaymentMethod
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
     * @ORM\Column(type="string")
     */
    private $address;

    /**
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $destTag;

    /**
     * @ORM\Column(type="decimal", precision=16, scale=8)
     */
    private $amount;

    /**
     * @ORM\Column(type="integer")
     */
    private $confirmations = 0;

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
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress(string $address): void
    {
        $this->address = $address;
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
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getDestTag(): ?string
    {
        return $this->destTag;
    }

    /**
     * @param string|null $destTag
     */
    public function setDestTag(?string $destTag): void
    {
        $this->destTag = $destTag;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getConfirmations(): float
    {
        return $this->confirmations;
    }

    /**
     * @param int $confirmations
     */
    public function setConfirmations(int $confirmations): void
    {
        $this->confirmations = $confirmations;
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
            'type' => $this->getType(),
            'address' => $this->getAddress(),
            'dest_tag' => $this->getDestTag(),
            'amount' => $this->getAmount(),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}