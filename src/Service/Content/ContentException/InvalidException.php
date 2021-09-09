<?php
declare(strict_types=1);

namespace App\Service\Content\ContentException;

class InvalidException extends \Exception
{
    protected $code = 400;
    protected $message = 'Invalid file type.';
}
