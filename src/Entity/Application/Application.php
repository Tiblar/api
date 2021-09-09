<?php
namespace App\Entity\Application;

use App\Entity\Application\OAuth\RedirectURL;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Entity\Application\AppRepository")
 * @ORM\Table(
 *     indexes={
 *         @ORM\Index(name="application_user_id_idx", columns={"user_id"}),
 *         @ORM\Index(name="application_client_id_idx", columns={"id", "client_secret"})
 *     },
 *     name="application"
 * )
 */
class Application
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private string $id;

    /**
     * @ORM\Column(type="string")
     */
    private string $userId;

    /**
     * @ORM\Column(type="string")
     */
    private string $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $icon = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="string")
     */
    private string $clientSecret;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Application\OAuth\RedirectURL", mappedBy="client", fetch="EAGER", cascade={"remove", "persist"}, orphanRemoval=true
     * )
     * @ORM\JoinColumn(name="redirect_urls_id", referencedColumnName="id")
     */
    protected Collection $redirectURLs;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTime $timestamp;

    public function __construct()
    {
        $this->clientSecret = \bin2hex(\openssl_random_pseudo_bytes(16));
        $this->redirectURLs = new ArrayCollection();
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param string|null $icon
     */
    public function setIcon(?string $icon): void
    {
        $this->icon = $icon;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * @param string $clientSecret
     */
    public function setClientSecret(string $clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * @return Collection
     */
    public function getRedirectURLs(): Collection
    {
        return $this->redirectURLs;
    }

    private function getRedirectURLsArray(): array
    {
        $urls = [];
        foreach($this->getRedirectURLs()->toArray() as $url){
            $urls[] = $url->toArray();
        }

        return $urls;
    }

    /**
     * @param RedirectURL $redirectURL
     */
    public function addRedirectURL(RedirectURL $redirectURL): void
    {
        $this->redirectURLs->add($redirectURL);
    }

    /**
     * @param RedirectURL[] $urls
     */
    public function setAttachments(array $urls): void
    {
        foreach($urls as $url){
            $this->addRedirectURL($url);
        }
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
            'user_id' => $this->getUserId(),
            'name' => $this->getName(),
            'icon' => $this->getIcon(),
            'description' => $this->getDescription(),
            'client_secret' => $this->getClientSecret(),
            'redirect_urls' => $this->getRedirectURLsArray(),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}