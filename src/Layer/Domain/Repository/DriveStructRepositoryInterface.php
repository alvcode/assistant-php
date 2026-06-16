<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Dict\Drive\DriveStructTypeEnum;
use App\Layer\Domain\Entity\DriveStructEntity;
use App\Layer\Infrastructure\DTO\Drive\DriveTreeDTO;

interface DriveStructRepositoryInterface
{
    /** @return DriveTreeDTO[] */
    public function getTreeByUserID(int $userID, ?int $parentID): array;

    public function findRow(
        int $userId,
        string $name,
        DriveStructTypeEnum $type,
        ?int $parentId = null,
    ): ?DriveStructEntity;

    public function save(DriveStructEntity $entity): DriveStructEntity;

    public function getById(int $id): ?DriveStructEntity;

    public function deleteRecursive(int $structId, int $userId): void;

    /** @param int[] $structIds */
    public function structCountByUserAndIds(int $userId, array $structIds): int;

    /** @param int[] $structIds */
    public function massUpdateParentId(?int $parentId, array $structIds): void;
}
