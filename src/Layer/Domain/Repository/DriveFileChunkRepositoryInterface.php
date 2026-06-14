<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Entity\DriveFileChunkEntity;

interface DriveFileChunkRepositoryInterface
{
    /** @return DriveFileChunkEntity[] */
    public function getAllRecursive(int $structId, int $userId): array;
}