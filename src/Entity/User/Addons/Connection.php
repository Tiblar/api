<?php
namespace App\Entity\User\Addons;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="user_id", columns={"user_id"}),
 *  }, name="user_service_connection")
 */
class Connection
{
    static $SERVICE_DISCORD = "discord";
    static $SERVICE_PAYPAL = "paypal";

    /**
     * @ORM\Id
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * User of connection
     *
     * @ORM\Column(type="string")
     */
    private $userId;

    /**
     * Steam
     * Discord
     * Twitch
     * YouTube
     * MAL
     * PayPal
     *
     * @ORM\Column(type="string")
     */
    private $service;

    /**
     * Account of user
     *
     * @ORM\Column(type="string")
     */
    private $account;

    /**
     * Link to account
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $link;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getService()
    {
        return $this->service;
    }

    public function setService($service)
    {
        $this->service = $service;
    }

    public function getAccount()
    {
        return $this->account;
    }

    public function setAccount($account)
    {
        $this->account = $account;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function setLink($link)
    {
        $this->link = $link;
    }

    function toArray(): array
    {
        return [
          'service' => $this->getService(),
          'account' => $this->getAccount(),
          'link' => $this->getLink(),
        ];
    }
}
