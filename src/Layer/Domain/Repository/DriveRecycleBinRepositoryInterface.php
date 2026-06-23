<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

interface DriveRecycleBinRepositoryInterface
{
    public function upsert(int $structId, string $originalPath, \DateTimeImmutable $createdAt): void;
}
