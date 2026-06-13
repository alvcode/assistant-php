<?php

declare(strict_types=1);

namespace App\Layer\Application\DTO\Drive;

final readonly class DriveCreateDirectoryDTO
{
    public function __construct(
        public string $name,
        public ?int $parentId,
    ) {}
}
