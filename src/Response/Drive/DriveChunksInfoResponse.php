<?php

declare(strict_types=1);

namespace App\Response\Drive;

final readonly class DriveChunksInfoResponse
{
    public function __construct(
        public ?int $start_number,
        public ?int $end_number,
    ) {}
}