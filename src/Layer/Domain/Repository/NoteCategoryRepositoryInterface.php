<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Entity\NoteCategoryEntity;

interface NoteCategoryRepositoryInterface
{
    public function save(NoteCategoryEntity $entity): NoteCategoryEntity;

    public function getById(int $id): ?NoteCategoryEntity;

    public function getMaxPosition(int $userId, ?int $parentId): int;

    /** @return NoteCategoryEntity[] */
    public function getAllByUserId(int $userId): array;

    /** @return NoteCategoryEntity[] */
    public function getByIDAndUserWithChildren(int $id, int $userId): array;

    /**
     * @param int[] $ids
     */
    public function deleteByIDs(array $ids): void;
}
