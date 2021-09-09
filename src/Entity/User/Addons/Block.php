<?php
namespace App\Entity\User\Addons;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Entity\User\UserRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="block_blocker_idx", columns={"blocker_id"}),
 *     @ORM\Index(name="block_blocked_idx", columns={"blocked_id"}),
 *  },
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="block_unique", columns={"blocker_id", "blocked_id"})
 *  })
 */
class Block
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
    private $blockerId;

    /**
     * @ORM\Column(type="string")
     */
    private $blockedId;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @ORM\Version
     * @var \DateTime
     */
    private $timestamp = null;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getBlockerId(): string
    {
        return $this->blockerId;
    }

    public function setBlockerId(string $blockerId)
    {
        $this->blockerId = $blockerId;
    }

    public function getBlockedId(): string
    {
        return $this->blockedId;
    }

    public function setBlockedId(string $blockedId)
    {
        $this->blockedId = $blockedId;
    }

    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    public function toArray(): array
    {
        return [
            'blocker_id' => $this->getBlockerId(),
            'blocked_id' => $this->getBlockedId(),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}
