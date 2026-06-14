<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Factory\Drive;

use App\Layer\Domain\Entity\DriveFileEntity;
use App\Layer\Domain\Service\Utils\DateTimeImmutable;
use App\Layer\Domain\ValueObject\FileSizeVO;

final readonly class DriveFileFactory
{
    public function getNewDriveFile(
        int $driveStructId, 
        ?string $path, 
        string $ext, 
        FileSizeVO $size, 
        bool $isChunk, 
        ?string $sha256
    ): DriveFileEntity
    {
        return new DriveFileEntity(
            id: null,
            driveStructId: $driveStructId,
            path: $path,
            ext: $ext,
            size: $size,
            createdAt: DateTimeImmutable::createNowUtc(),
            isChunk: $isChunk,
            sha256: $sha256
        );
    }
}