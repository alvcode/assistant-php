<?php

declare(strict_types=1);

namespace App\Layer\Application\DTO\User;

final readonly class RefreshTokenDTO
{
    public function __construct(
        public string $token,
        public string $refreshToken,
    ) {}
}
