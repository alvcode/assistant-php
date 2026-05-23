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
}
