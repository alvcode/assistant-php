<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Entity\DriveFileChunkEntity;
use App\Layer\Domain\ValueObject\FileSizeVO;
use App\Layer\Infrastructure\DTO\Drive\DriveChunksInfoDTO;

interface DriveFileChunkRepositoryInterface
{
    /** @return DriveFileChunkEntity[] */
    public function getAllRecursive(int $structId, int $userId): array;

    public function getChunksSize(int $driveFileId): FileSizeVO;

    public function save(DriveFileChunkEntity $entity): DriveFileChunkEntity;

    public function getChunksInfo(int $fileId): DriveChunksInfoDTO;

    public function getByFileIDAndNumber(int $fileId, int $chunkNumber): ?DriveFileChunkEntity;
}