<?php
declare(strict_types=1);

namespace App\Entity\Media;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Entity\Media\MediaRepository")
 */
class AudioWave
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
    private $hash;

    /**
     * @ORM\Column(type="json")
     */
    private $data;

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
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return json_decode($this->data);
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = json_encode($data);
    }

    /**
     * Size returns GB
     *
     * @return array
     */
    function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'hash' => $this->getHash(),
            'data' => $this->getData(),
            'size' => number_format(strlen(json_encode($this->getData())) / 1024 / 1024, 8)
        ];
    }
}