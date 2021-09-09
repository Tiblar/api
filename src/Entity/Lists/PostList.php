<?php
namespace App\Entity\Lists;

use App\Entity\User\User;
use App\Structure\User\SanitizedUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="post_list", indexes={
 *     @ORM\Index(name="post_list_idx", columns={"list_user_id", "visibility"}),
 * })
 */
class PostList
{
    static string $VISIBILITY_PUBLIC = "PUBLIC";
    static string $VISIBILITY_PRIVATE = "PRIVATE";

    /**
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="App\Service\Generator\Snowflake")
     */
    private $id;

    /**
     * Who owns the list
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", fetch="EAGER")
     * @ORM\JoinColumn(name="list_user_id", referencedColumnName="id")
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $title;

    /**
     * @ORM\Column(type="string", nullable=true, length=400)
     */
    private $description;

    /**
     * @ORM\Column(type="string")
     */
    private $visibility;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

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
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return SanitizedUser
     */
    public function getAuthor(): SanitizedUser
    {
        return new SanitizedUser($this->author);
    }

    /**
     * @param User $author
     */
    public function setAuthor(User $author): void
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
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
    public function getVisibility(): string
    {
        return $this->visibility;
    }

    /**
     * @param string $visibility
     */
    public function setVisibility(string $visibility)
    {
        if(
        in_array($visibility, [
            self::$VISIBILITY_PRIVATE,
            self::$VISIBILITY_PUBLIC
        ])
        ){
            $this->visibility = $visibility;
        }else{
            throw new \Exception("Invalid currency type");
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
            'author' => $this->getAuthor()->toArray(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'visibility' => $this->getVisibility(),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}