<?php
declare(strict_types=1);

namespace App\Structure\User;

use App\Entity\User\Addons\ConfirmEmail;

/**
 * Confirm email without sensitive information.
 */
class SanitizedConfirmEmail
{
    private $email = null;

    private $expireTimestamp = null;

    public function __construct(ConfirmEmail $confirm)
    {
        $this->email = $confirm->getEmail();
        $this->expireTimestamp = $confirm->getExpireTimestamp();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getExpireTimestamp(): \DateTime
    {
        return $this->expireTimestamp;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'email' => $this->getEmail(),
            'expire_timestamp' => $this->getExpireTimestamp()->format('c'),
        ];
    }
}