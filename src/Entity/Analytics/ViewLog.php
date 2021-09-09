<?php
namespace App\Entity\Analytics;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Entity\Analytics\AnalyticsRepository")
 * @ORM\Table(
 *     indexes={@ORM\Index(name="log_idx", columns={"resource_id", "ip_address", "type", "expire_timestamp", "timestamp"})},
 *     indexes={@ORM\Index(name="log_user_idx", columns={"user_id", "timestamp"})},
 *     indexes={@ORM\Index(name="log_ip_address_idx", columns={"ip_address", "timestamp"})},
 *     name="analytics_view_log"
 * )
 */
class ViewLog
{
    static string $VIEW_TYPE_POST = "VIEW_TYPE_POST";
    static string $VIEW_TYPE_USER = "VIEW_TYPE_USER";

    static string $VIEW_SOURCE_DASHBOARD = "DASHBOARD";
    static string $VIEW_SOURCE_NEWEST = "NEWEST";
    static string $VIEW_SOURCE_TRENDING = "TRENDING";
    static string $VIEW_SOURCE_PROFILE = "PROFILE";
    static string $VIEW_SOURCE_DIRECT = "DIRECT";
    static string $VIEW_SOURCE_SEARCH = "SEARCH";

    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private string $id;

    /**
     * Id of post or user viewed
     *
     * @ORM\Column(type="bigint")
     */
    private string $resourceId;

    /**
     * User who owns the resource
     *
     * @ORM\Column(type="string")
     */
    private string $userId;

    /**
     * Source of view (dashboard, profile, etc)
     *
     * @ORM\Column(type="string")
     */
    private string $source;

    /**
     * IP of viewer
     *
     * @ORM\Column(type="string")
     */
    private string $ipAddress;

    /**
     * Type of view event
     *
     * @ORM\Column(type="string")
     */
    private string $type;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $expireTimestamp;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $timestamp;

    public function __construct()
    {
        $this->timestamp = new \DateTime();
    }

    /**
     * @return array
     */
    public static function getSourceTypes(): array
    {
        return [
            self::$VIEW_SOURCE_DASHBOARD,
            self::$VIEW_SOURCE_NEWEST,
            self::$VIEW_SOURCE_TRENDING,
            self::$VIEW_SOURCE_PROFILE,
            self::$VIEW_SOURCE_DIRECT,
            self::$VIEW_SOURCE_SEARCH,
        ];
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
    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    /**
     * @param string $resourceId
     */
    public function setResourceId(string $resourceId): void
    {
        $this->resourceId = $resourceId;
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
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     * @throws \Exception
     */
    public function setSource(string $source): void
    {
        if(
        in_array($source, self::getSourceTypes())
        ){
            $this->source = $source;
        }else{
            throw new \Exception("Invalid view log source");
        }
    }

    /**
     * @return string
     */
    public function getIPAddress(): string
    {
        return $this->ipAddress;
    }

    /**
     * @param string $ipAddress
     */
    public function setIPAddress(string $ipAddress): void
    {
        $this->ipAddress = $ipAddress;
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
     * @throws \Exception
     */
    public function setType(string $type): void
    {
        if(
            in_array($type, [
                self::$VIEW_TYPE_POST,
                self::$VIEW_TYPE_USER,
            ])
        ){
            $this->type = $type;
        }else{
            throw new \Exception("Invalid view log type");
        }
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
            'resource_id' => $this->getResourceId(),
            'user_id' => $this->getUserId(),
            'ip_address' => $this->getIPAddress(),
            'type' => $this->getType(),
            'timestamp' => $this->getTimestamp()->format('c')
        ];
    }
}
