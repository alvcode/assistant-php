<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Drive;

use App\Layer\Application\Exception\Drive\DriveFileNotFoundException;
use App\Layer\Application\Exception\Drive\DriveStructIsNotChunkException;
use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Domain\Repository\DriveFileChunkRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;

final readonly class DriveChunkEndUseCase
{
    public function __construct(
        private DriveFileRepositoryInterface $driveFileRepository,
        private DriveStructRepositoryInterface $driveStructRepository,
        private DriveFileChunkRepositoryInterface $driveFileChunkRepository,
    ) {}

    /**
     * @throws DriveStructNotFoundException
     * @throws DriveFileNotFoundException
     * @throws DriveStructIsNotChunkException
     */
    public function handle(int $structId, int $userId): void
    {
        $driveStructEntity = $this->driveStructRepository->getById($structId, false);
        if (\is_null($driveStructEntity) || $driveStructEntity->getUserId() !== $userId) {
            throw new DriveStructNotFoundException('Структура не найдена');
        }

        $driveFileEntity = $this->driveFileRepository->getByStructId($driveStructEntity->getId());
        if (\is_null($driveFileEntity)) {
            throw new DriveFileNotFoundException('Файл не найден');
        }
        if (!$driveFileEntity->isChunk()) {
            throw new DriveStructIsNotChunkException('Файл не является загруженным с помощью чанков');
        }

        $fileSize = $this->driveFileChunkRepository->getChunksSize($driveFileEntity->getId());

        $driveFileEntity->setSize($fileSize);
        $this->driveFileRepository->save($driveFileEntity);
    }
}
