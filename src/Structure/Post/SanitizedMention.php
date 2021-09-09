<?php
namespace App\Structure\Post;

use App\Structure\User\SanitizedUser;

class SanitizedMention
{
    private $id;

    private $postId;

    private $user;

    private $indices;

    public function __construct(array $arr)
    {
        if(isset($arr['id'])){
            $this->setId($arr['id']);
        }

        if(isset($arr['postId'])){
            $this->setPostId($arr['postId']);
        }

        if(isset($arr['user']) && $arr['user'] INSTANCEOF SanitizedUser){
            $this->setUser($arr['user']);
        }

        if(isset($arr['indices']) && is_array($arr['indices'])){
            $this->setIndices($arr['indices']);
        }

        if(isset($arr['indices']) && is_string($arr['indices'])){
            $decode = json_decode($arr['indices']);
            if(is_array($decode)){
                $this->setIndices($decode);
            }
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
     * @return SanitizedUser
     */
    public function getUser(): SanitizedUser
    {
        return $this->user;
    }

    /**
     * @param SanitizedUser $user
     */
    public function setUser(SanitizedUser $user): void
    {
        $this->user = $user;
    }

    /**
     * @param array $indices
     */
    public function setIndices(array $indices): void
    {
        $this->indices = $indices;
    }

    /**
     * @return array
     */
    public function getIndices()
    {
        return $this->indices;
    }

    /**
     * @return array
     */
    function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'user' => $this->getUser()->toArray(),
            'indices' => $this->getIndices(),
        ];
    }
}