<?php

declare(strict_types=1);

namespace App\Layer\Application\DTO\User;

final readonly class LoginAndPasswordDTO
{
    public function __construct(
        public string $login,
        public string $password
    ) {}
}
