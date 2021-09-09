<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={@ORM\Index(name="captcha_idx", columns={"id"})})
 * @ORM\Table(indexes={@ORM\Index(name="captcha_solve_idx", columns={"id", "code", "consumed"})})
 */
class Captcha
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
    private $code;

    /**
     * @ORM\Column(type="boolean")
     */
    private $consumed = false;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expireTimestamp;

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
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function isConsumed(): string
    {
        return $this->consumed;
    }

    /**
     * @param bool $consumed
     */
    public function setConsumed(bool $consumed): void
    {
        $this->consumed = $consumed;
    }

    /**
     * @return \DateTime
     */
    public function getExpireTimestamp(): \DateTime
    {
        return $this->expireTimestamp;
    }

    /**
     * @param \DateTime $expireTimestamp
     */
    public function setExpireTimestamp(\DateTime $expireTimestamp): void
    {
        $this->expireTimestamp = $expireTimestamp;
    }
}