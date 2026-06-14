<?php

declare(strict_types=1);

namespace App\Layer\Application\DTO\Drive;

final readonly class DriveUploadFileDTO
{
    public function __construct(
        public ?int $parentId = null,
        public ?string $sha256 = null,
    ) {}
}