<?php

namespace App\Event;

use App\Http\ApiResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;

/**
 * Class JWTAuthenticationSuccessListener
 * @package App\EventListener
 */
class JWTNotFoundListener
{

    /**
     * @param JWTNotFoundEvent $event
     */
    public function onJWTNotFound(JWTNotFoundEvent $event)
    {
        $apiResponse = new ApiResponse("Auth token not found.", null, [], 403);

        $event->setResponse($apiResponse);
    }
}