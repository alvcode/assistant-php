<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Entity\DriveArchiveFileChunkEntity;

interface DriveArchiveFileChunkRepositoryInterface
{
    public function save(DriveArchiveFileChunkEntity $entity): DriveArchiveFileChunkEntity;
}
