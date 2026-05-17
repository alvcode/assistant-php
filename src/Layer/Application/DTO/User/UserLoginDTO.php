<?php

declare(strict_types=1);

namespace App\Layer\Application\DTO\User;

final readonly class UserLoginDTO
{
    public function __construct(
        public int $userId,
        public string $token,
        public string $refreshToken,
        public int $expiredTo,
    ) {}
}
