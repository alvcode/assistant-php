<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Entity\Aggregate\DriveRecycleBinAggregate;
use App\Layer\Domain\Entity\DriveRecycleBinEntity;

interface DriveRecycleBinRepositoryInterface
{
    public function upsert(int $structId, string $originalPath, \DateTimeImmutable $createdAt): void;

    public function deleteAllChildren(int $parentStructId, int $userId): void;

    /** @return DriveRecycleBinEntity[] */
    public function getAllChildren(int $parentStructId, int $userId): array;

    /** @return DriveRecycleBinAggregate[] */
    public function getAll(int $userId): array;

    public function getById(int $id): ?DriveRecycleBinEntity;

    public function delete(DriveRecycleBinEntity $entity): void;
}
