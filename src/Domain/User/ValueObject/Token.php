<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

class Token
{

    public function __construct(private string $token) {}

    public function getToken(): string
    {
        return $this->token;
    }
}
