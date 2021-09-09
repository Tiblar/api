<?php
namespace App\Exception;

class RateLimitException extends \Exception {
    protected $message = "You are requesting too much.";
}