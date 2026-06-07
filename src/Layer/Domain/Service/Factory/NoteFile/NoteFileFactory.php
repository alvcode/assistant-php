<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Factory\NoteFile;

use App\Layer\Domain\Dict\Common\FileSizeTypeEnum;
use App\Layer\Domain\Entity\NoteFileEntity;
use App\Layer\Domain\Service\Utils\DateTimeImmutable;
use App\Layer\Domain\Service\Utils\HasherServiceInterface;
use App\Layer\Domain\ValueObject\FileSizeVO;

final readonly class NoteFileFactory
{
    public function __construct(
        private HasherServiceInterface $hasherService,
    ) {}

    public function getNewNoteFile(
        int $userID,
        string $originalFilename,
        string $filePath,
        string $ext,
        int $sizeInBytes
    ): NoteFileEntity
    {
        return new NoteFileEntity(
            id: null,
            userID: $userID,
            originalFilename: $originalFilename,
            filePath: $filePath,
            ext: $ext,
            size: new FileSizeVO(size: (float)$sizeInBytes, sizeType: FileSizeTypeEnum::Bytes),
            hash: $this->hasherService->generateRandomStringWithoutSymbols(80),
            createdAt: DateTimeImmutable::createNowUtc(),
        );
    }
}
