<?php

declare(strict_types=1);

namespace App\Layer\Application\DTO\Drive;

use App\Layer\Domain\ValueObject\FileSizeVO;

final readonly class DriveChunkPrepareDTO
{
    public function __construct(
        public string $fileName,
        public FileSizeVO $size,
        public ?int $parentId,
        public ?string $sha256,
    ) {}
}