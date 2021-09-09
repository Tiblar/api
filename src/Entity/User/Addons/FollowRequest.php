<?php
namespace App\Entity\User\Addons;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="follow_request_requester_idx", columns={"requester_id"}),
 *     @ORM\Index(name="follow_request_requested_idx", columns={"requested_id"}),
 * })
 */
class FollowRequest
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
    private $requesterId;

    /**
     * @ORM\Column(type="string")
     */
    private $requestedId;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

    public function __construct()
    {
        $this->timestamp = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getRequesterId()
    {
        return $this->requesterId;
    }

    public function setRequesterId($requesterId)
    {
        $this->requesterId = $requesterId;
    }

    public function getRequestedId()
    {
        return $this->requestedId;
    }

    public function setRequestedId($requestedId)
    {
        $this->requestedId = $requestedId;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function toArray(): array
    {
        return [
          'id' => $this->getId(),
          'requester_id' => $this->getRequesterId(),
          'requested_id' => $this->getRequestedId(),
          'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}
