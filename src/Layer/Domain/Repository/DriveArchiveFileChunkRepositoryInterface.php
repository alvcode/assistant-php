<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Entity\DriveArchiveFileChunkEntity;
use App\Layer\Domain\Repository\DTO\Drive\DriveChunksInfoDTO;

interface DriveArchiveFileChunkRepositoryInterface
{
    public function save(DriveArchiveFileChunkEntity $entity): DriveArchiveFileChunkEntity;

    public function deleteByDriveArchiveFileId(int $driveArchiveFileId): void;

    public function getChunksInfo(int $driveArchiveFileId): DriveChunksInfoDTO;
}
