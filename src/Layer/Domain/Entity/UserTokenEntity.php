<?php

declare(strict_types=1);

namespace App\Layer\Domain\Entity;

final class UserTokenEntity
{
    public function __construct(
        private int $userId,
        private string $token,
        private string $refreshToken,
        private int $expiredTo,
    ) {}

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    public function getExpiredTo(): int
    {
        return $this->expiredTo;
    }

    public function setExpiredTo(int $expiredTo): void
    {
        $this->expiredTo = $expiredTo;
    }
}
