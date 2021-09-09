<?php
namespace App\Structure\Application;

use App\Entity\Application\Application;

class SanitizedApplication extends Application
{
    public function __construct(array $arr)
    {
        parent::__construct();

        if(isset($arr['id'])){
            $this->setId($arr['id']);
        }

        if(isset($arr['userId'])){
            $this->setUserId($arr['userId']);
        }

        if(isset($arr['name'])){
            $this->setName($arr['name']);
        }

        if(isset($arr['icon'])){
            $this->setIcon($arr['icon']);
        }

        if(isset($arr['description'])){
            $this->setDescription($arr['description']);
        }

        if(isset($arr['clientSecret'])){
            $this->setClientSecret($arr['clientSecret']);
        }

        if(isset($arr['redirectURLs'])){
            foreach($arr['redirectURLs'] as $url){
                $this->redirectURLs->add($url);
            }
        }

        if(isset($arr['timestamp'])){
            $this->setTimestamp($arr['timestamp']);
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'user_id' => $this->getUserId(),
            'name' => $this->getName(),
            'icon' => $this->getIcon(),
            'description' => $this->getDescription(),
            'redirect_urls' => $this->getRedirectURLs()->toArray(),
            'timestamp' => $this->getTimestamp()->format('c'),
        ];
    }
}