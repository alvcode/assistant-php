<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Factory\Drive;

use App\Layer\Domain\Dict\Common\FileSizeTypeEnum;
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

    public function getForPrepareChunk(
        int $driveStructId,
        string $ext,
        ?string $sha256,
    ): DriveFileEntity
    {
        return new DriveFileEntity(
            id: null,
            driveStructId: $driveStructId,
            path: null,
            ext: $ext,
            size: new FileSizeVO(size: 0, sizeType: FileSizeTypeEnum::Bytes),
            createdAt: DateTimeImmutable::createNowUtc(),
            isChunk: true,
            sha256: $sha256
        );
    }
}