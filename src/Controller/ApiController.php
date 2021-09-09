<?php
namespace App\Controller;

use App\Http\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiController extends AbstractController
{
    /**
     * Returns a JSON response
     *
     * @param array $data
     * @param string|null $message
     * @param int $statusCode
     * @param array $headers
     *
     * @return JsonResponse
     */
    public function respond(array $data, string $message = null, int $statusCode = 200, array $headers = [])
    {
        return new ApiResponse($message, $data, null, $statusCode, $headers);
    }

    /**
     * Sets an error message and returns a JSON response
     *
     * @param array $errors
     * @param null $message
     * @param int $statusCode
     * @param array $headers
     *
     * @return JsonResponse
     */
    public function respondWithErrors(array $errors, $message = null, int $statusCode = 400, $headers = [])
    {
        return new ApiResponse($message, null, $errors, $statusCode, $headers);
    }

    /**
     * @param array $data
     * @param array $cookies
     * @param string|null $message
     * @param int $statusCode
     * @param array $headers
     * @return ApiResponse
     */
    public function respondWithCookies(array $data, array $cookies, string $message = null, int $statusCode = 200, array $headers = [])
    {
        $response = new ApiResponse($message, $data, null, $statusCode, $headers);

        foreach($cookies as $cookie){
            if($cookie INSTANCEOF Cookie){
                $response->headers->setCookie($cookie);
            }
        }

        return $response;
    }
}
