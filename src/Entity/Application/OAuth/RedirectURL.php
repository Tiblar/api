<?php
namespace App\Entity\Application\OAuth;

use App\Entity\Application\Application;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     indexes={
 *         @ORM\Index(name="redirect_url_idx", columns={"client_id", "url"}),
 *     },
 *     name="oauth_redirect_url"
 * )
 */
class RedirectURL
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private string $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Application\Application", inversedBy="redirectURLs")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    private Application $client;

    /**
     * @ORM\Column(type="string")
     */
    private string $url;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $timestamp;

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
     * @return Application
     */
    public function getClient(): Application
    {
        return $this->client;
    }

    /**
     * @param Application $client
     */
    public function setClient(Application $client): void
    {
        $this->client = $client;
    }

    /**
     * @return string
     */
    public function getURL(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setURL(string $url): void
    {
        $this->url = $url;
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
            'url' => $this->getURL(),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}