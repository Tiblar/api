<?php
namespace App\Http;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse extends JsonResponse
{
    /**
     * ApiResponse constructor.
     *
     * @param string $message
     * @param array|null  $data
     * @param array|null  $errors
     * @param int    $status
     * @param array  $headers
     * @param bool   $json
     */
    public function __construct(string $message = null, ?array $data = [], ?array $errors = [], int $status = 200, array $headers = [], bool $json = false)
    {
        $headers[] = [
            'Content-Type' => 'application/json',
        ];

        parent::__construct($this->format($status, $message, $data, $errors), $status, $headers, $json);

        $this->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    /**
     * Formatter the API response.
     *
     * @param string $message
     * @param mixed  $data
     * @param array  $errors
     *
     * @return array
     */
    private function format(int $status, string $message = null, ?array $data = [], ?array $errors = [])
    {
        $response = [
            'status_code' => $status,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return $response;
    }
}