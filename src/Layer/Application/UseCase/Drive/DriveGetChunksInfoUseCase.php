<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Drive;

use App\Layer\Application\Exception\Drive\DriveFileNotFoundException;
use App\Layer\Application\Exception\Drive\DriveStructIsNotChunkException;
use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Domain\Repository\DriveFileChunkRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Repository\DTO\Drive\DriveChunksInfoDTO;

final readonly class DriveGetChunksInfoUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
        private DriveFileRepositoryInterface $driveFileRepository,
        private DriveFileChunkRepositoryInterface $driveFileChunkRepository,
    ) {}

    /**
     * @throws DriveStructNotFoundException
     * @throws DriveFileNotFoundException
     * @throws DriveStructIsNotChunkException
     */
    public function handle(int $structId, int $userId): DriveChunksInfoDTO
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

        return $this->driveFileChunkRepository->getChunksInfo($driveFileEntity->getId());
    }
}
