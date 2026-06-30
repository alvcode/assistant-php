<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository\DTO\Drive;

final readonly class DriveTreeDTO
{
    public function __construct(
        public int $id,
        public int $userId,
        public string $name,
        public int $type,
        public int $size,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
        public bool $isChunk,
        public ?string $sha256,
    ) {}
}
