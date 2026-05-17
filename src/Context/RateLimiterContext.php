<?php

declare(strict_types=1);

namespace App\Context;

final class RateLimiterContext
{
    public int $limit;
    public int $remaining;

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function getRemaining(): int
    {
        return $this->remaining;
    }

    public function setRemaining(int $remaining): void
    {
        $this->remaining = $remaining;
    }
}
