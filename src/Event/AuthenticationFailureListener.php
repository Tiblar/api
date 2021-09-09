<?php
namespace App\Event;

use App\Http\ApiResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;

class AuthenticationFailureListener
{
    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event)
    {
        $apiResponse = new ApiResponse($event->getException()->getMessage(), null, [], 401);

        $event->setResponse($apiResponse);
    }

}
