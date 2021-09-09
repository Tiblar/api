<?php
namespace App\Entity\SpamFilter;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="spam_filter_word_list", indexes={
 *      @ORM\Index(name="token_idx", columns={"token"}),
 * })
 */
class WordList
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
    private string $token;

    /**
     * @ORM\Column(type="integer")
     */
    private int $countHam;

    /**
     * @ORM\Column(type="integer")
     */
    private int $countSpam;

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
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * @return int
     */
    public function countHam(): int
    {
        return $this->countHam;
    }

    /**
     * @param int $countHam
     */
    public function setCountHam(int $countHam): void
    {
        $this->countHam = $countHam;
    }

    /**
     * @return int
     */
    public function countSpam(): int
    {
        return $this->countSpam;
    }

    /**
     * @param int $countSpam
     */
    public function setCountSpam(int $countSpam): void
    {
        $this->countSpam = $countSpam;
    }
}