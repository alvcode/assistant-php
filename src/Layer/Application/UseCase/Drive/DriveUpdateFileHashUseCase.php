<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Drive;

use App\Layer\Application\Exception\Drive\DriveFileNotFoundException;
use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Domain\Dict\Drive\DriveStructTypeEnum;
use App\Layer\Domain\Repository\DriveFileRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;

final readonly class DriveUpdateFileHashUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
        private DriveFileRepositoryInterface $driveFileRepository,
    ) {}

    /**
     * @throws DriveStructNotFoundException
     * @throws DriveFileNotFoundException
     */
    public function handle(int $structId, string $sha256, int $userId): void
    {
        $driveStructEntity = $this->driveStructRepository->getById($structId);
        if (\is_null($driveStructEntity) || $driveStructEntity->getUserId() !== $userId) {
            throw new DriveStructNotFoundException('Структура не найдена');
        }

        if ($driveStructEntity->getType() !== DriveStructTypeEnum::File) {
            throw new DriveFileNotFoundException('Структура не является файлом');
        }

        $driveFileEntity = $this->driveFileRepository->getByStructId($driveStructEntity->getId());
        if (\is_null($driveFileEntity) || !$driveFileEntity->isChunk()) {
            throw new DriveFileNotFoundException('Файл не найден');
        }

        $driveFileEntity->setSha256($sha256);
        $this->driveFileRepository->save($driveFileEntity);
    }
}