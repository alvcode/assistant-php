<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveArchive;

use App\Layer\Application\DTO\Common\FileDTO;
use App\Layer\Application\Exception\DriveArchive\DriveArchiveChunkNotFoundException;
use App\Layer\Application\Exception\DriveArchive\DriveArchiveFileNotFoundException;
use App\Layer\Application\Exception\DriveArchive\DriveArchiveJobNotFoundException;
use App\Layer\Application\Exception\DriveArchive\DriveArchiveNotCompletedJobException;
use App\Layer\Domain\Dict\Drive\DriveArchiveJobStatusEnum;
use App\Layer\Domain\Repository\DriveArchiveFileChunkRepositoryInterface;
use App\Layer\Domain\Repository\DriveArchiveRepositoryInterface;
use App\Layer\Domain\Service\Factory\Storage\StorageRepositoryFactoryInterface;

final readonly class DriveArchiveGetChunkByNumberUseCase
{
    public function __construct(
        private DriveArchiveRepositoryInterface $driveArchiveRepository,
        private DriveArchiveFileChunkRepositoryInterface $driveArchiveFileChunkRepository,
        private StorageRepositoryFactoryInterface $storageRepositoryFactory,
    ) {}

    /**
     * @throws DriveArchiveFileNotFoundException
     * @throws DriveArchiveJobNotFoundException
     * @throws DriveArchiveNotCompletedJobException
     * @throws DriveArchiveChunkNotFoundException
     */
    public function handle(int $driveArchiveJobId, int $chunkNumber, int $userId): FileDTO
    {
        $driveArchiveJobEntity = $this->driveArchiveRepository->getJobById($driveArchiveJobId);
        if (!$driveArchiveJobEntity || $driveArchiveJobEntity->getUserId() !== $userId) {
            throw new DriveArchiveJobNotFoundException('Archive Job не найден');
        }
        if ($driveArchiveJobEntity->getStatus() !== DriveArchiveJobStatusEnum::Completed) {
            throw new DriveArchiveNotCompletedJobException('Архив в незавершенном статусе');
        }

        $driveArchiveFileEntity = $this->driveArchiveRepository->getFileByJobId($driveArchiveJobEntity->getId());
        if (!$driveArchiveFileEntity) {
            throw new DriveArchiveFileNotFoundException('Запись о файле архива не найдена');
        }

        $driveArchiveChunkEntity = $this->driveArchiveFileChunkRepository->getByFileIdAndChunkNumber(
            fileId: $driveArchiveFileEntity->getId(),
            chunkNumber: $chunkNumber
        );
        if (!$driveArchiveChunkEntity) {
            throw new DriveArchiveChunkNotFoundException('Чанк не найден');
        }

        $file = $this->storageRepositoryFactory->getLocalStorage()->getFile($driveArchiveChunkEntity->getPath());
        return new FileDTO(
            file: $file->getFile(),
            originalExtension: $driveArchiveFileEntity->getExtension(),
            originalName: $driveArchiveFileEntity->getFileName(),
        );
    }
}
