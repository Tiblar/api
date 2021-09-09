<?php
namespace App\Structure\Media;

class SanitizedMagnet
{
    private $id;

    private $postId;

    private $magnet;

    public function __construct(array $arr)
    {
        if(isset($arr['id'])){
            $this->setId($arr['id']);
        }

        if(isset($arr['postId'])){
            $this->setPostId($arr['postId']);
        }

        if(isset($arr['magnet'])){
            $this->setMagnet($arr['magnet']);
        }
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
     * @return string
     */
    public function getPostId(): string
    {
        return $this->postId;
    }

    /**
     * @param string $postId
     */
    public function setPostId(string $postId): void
    {
        $this->postId = $postId;
    }

    /**
     * @return string
     */
    public function getMagnet(): string
    {
        return $this->magnet;
    }

    /**
     * @param string $magnet
     */
    public function setMagnet(string $magnet): void
    {
        $this->magnet = $magnet;
    }

    /**
     * @return array
     */
    function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'magnet' => $this->getMagnet(),
        ];
    }
}