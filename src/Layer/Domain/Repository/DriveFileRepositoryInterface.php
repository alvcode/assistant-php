<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Entity\DriveFileEntity;
use App\Layer\Domain\ValueObject\FileSizeVO;
use Generator;

interface DriveFileRepositoryInterface
{
    public function getUsedSpaceByUserID(int $userId): FileSizeVO;

    public function getLastId(): int;

    public function save(DriveFileEntity $entity): DriveFileEntity;

    public function getByStructId(int $structId): ?DriveFileEntity;

    /** @return DriveFileEntity[] */
    public function getAllRecursive(int $structId, int $userId): array;

    /**
     * @return Generator<int,DriveFileEntity>
     */
    public function getAll(): Generator;
}
