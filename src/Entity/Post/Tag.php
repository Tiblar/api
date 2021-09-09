<?php
namespace App\Entity\Post;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={@ORM\Index(name="tag_idx", columns={"title"})})
 */
class Tag
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private $id;

    /**
     * title of tag
     *
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * if a tag is NSFW
     *
     * @ORM\Column(type="boolean")
     */
    private $nsfw;

    /**
     * @ORM\Column(type="bigint", options={"default":"1"})
     */
    private $count;

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
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return bool
     */
    public function getNsfw(): bool
    {
        return $this->nsfw;
    }

    /**
     * @param bool $nsfw
     */
    public function setNsfw(bool $nsfw): void
    {
        $this->nsfw = $nsfw;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'nsfw' => $this->getNsfw(),
            'count' => $this->getCount(),
        ];
    }
}
