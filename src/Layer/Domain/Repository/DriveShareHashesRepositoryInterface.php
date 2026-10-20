<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Entity\DriveShareEntity;

interface DriveShareHashesRepositoryInterface
{
    public function existsByStructId(int $structId): bool;

    public function getByStructId(int $structId): ?DriveShareEntity;

    public function existsByHash(string $hash): bool;

    public function save(DriveShareEntity $entity): DriveShareEntity;

    public function deleteByStructId(int $structId): void;
}
