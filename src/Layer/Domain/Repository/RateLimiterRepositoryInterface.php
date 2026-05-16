<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

interface RateLimiterRepositoryInterface
{
    /**
     * @return int allowance
     */
    public function upsert(string $ip, int $allowance, int $nowTimestamp, int $durationSeconds): int;
}
