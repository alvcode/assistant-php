<?php

declare(strict_types=1);

namespace App\Layer\Application\DTO\Drive;

final readonly class DriveUploadChunkDTO
{
    public function __construct(
        public int $structId,
        public int $chunkNumber,
    ) {}
}