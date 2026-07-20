<?php

declare(strict_types=1);

namespace App\Response\LongPolling;

final readonly class PoolResponse
{
    public function __construct(
        public int $id,
        public string $eventKey,
        public array $payload,
    ) {}
}
