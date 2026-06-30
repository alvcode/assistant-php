<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Entity\Aggregate\DriveRecycleBinAggregate;

interface DriveRecycleBinRepositoryInterface
{
    public function upsert(int $structId, string $originalPath, \DateTimeImmutable $createdAt): void;

    /** @return DriveRecycleBinAggregate[] */
    public function getAll(int $userId): array;
}
