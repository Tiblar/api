<?php

namespace App\Event;

use App\Http\ApiResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    private $env;

    public function __construct($env)
    {
        $this->env = $env;
    }

    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $request   = $event->getRequest();

        $response = $this->createApiResponse($exception);

        $event->setResponse($response);
    }

    /**
     * Creates the ApiResponse from any Exception
     *
     * @param \Throwable $exception
     *
     * @return ApiResponse
     */
    private function createApiResponse(\Throwable $exception)
    {
        $message = $exception->getMessage();

        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
        $errors     = [];

        if($statusCode === 404 && $this->env === "prod"){
            $message = "This route does not exist.";
        }

        if($statusCode === 405 && $this->env === "prod"){
            $message = "This route does not exist for this method.";
        }

        if($statusCode === 500 && $this->env === "prod"){
            $message = "An error occurred that was not expected. Contact support if this continues.";
        }

        return new ApiResponse($message, null, $errors, $statusCode);
    }
}