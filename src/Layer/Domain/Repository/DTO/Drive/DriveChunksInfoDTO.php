<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository\DTO\Drive;

final readonly class DriveChunksInfoDTO
{
    public function __construct(
        public ?int $startNumber,
        public ?int $endNumber,
    ) {}
}
