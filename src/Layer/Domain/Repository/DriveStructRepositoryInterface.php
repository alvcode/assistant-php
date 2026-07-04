<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Dict\Drive\DriveStructTypeEnum;
use App\Layer\Domain\Entity\DriveStructEntity;
use App\Layer\Domain\Repository\DTO\Drive\DriveTreeDTO;

interface DriveStructRepositoryInterface
{
    /** @return DriveTreeDTO[] */
    public function getTreeByUserID(int $userID, ?int $parentID): array;

    public function findRow(
        int $userId,
        string $name,
        DriveStructTypeEnum $type,
        bool $includeRecycleBin,
        ?int $parentId = null,
    ): ?DriveStructEntity;

    public function save(DriveStructEntity $entity): DriveStructEntity;

    public function getById(int $id, bool $includeRecycleBin): ?DriveStructEntity;

    public function deleteRecursiveWithoutRecycleBin(int $structId, int $userId): void;

    /** @param int[] $structIds */
    public function structCountByUserAndIds(int $userId, array $structIds, bool $includeRecycleBin): int;

    /** @param int[] $structIds */
    public function massUpdateParentId(?int $parentId, array $structIds): void;

    /**
     * Возвращает рекурсивно саму структуру и те структуры в которые она входит в обратном порядке
     * @return DriveStructEntity[]
     */
    public function getAllRecursiveBackward(int $structId, int $userId): array;

    public function checkExistsByName(int $userId, string $name, ?int $parentId, ?int $excludeId): bool;
}
