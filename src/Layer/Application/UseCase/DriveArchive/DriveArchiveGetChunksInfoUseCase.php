<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveArchive;

use App\Layer\Application\Exception\DriveArchive\DriveArchiveFileNotFoundException;
use App\Layer\Application\Exception\DriveArchive\DriveArchiveJobNotFoundException;
use App\Layer\Application\Exception\DriveArchive\DriveArchiveNotCompletedJobException;
use App\Layer\Domain\Dict\Drive\DriveArchiveJobStatusEnum;
use App\Layer\Domain\Repository\DriveArchiveFileChunkRepositoryInterface;
use App\Layer\Domain\Repository\DriveArchiveRepositoryInterface;
use App\Layer\Domain\Repository\DTO\Drive\DriveChunksInfoDTO;
use Exception;

final readonly class DriveArchiveGetChunksInfoUseCase
{
    public function __construct(
        private DriveArchiveRepositoryInterface $driveArchiveRepository,
        private DriveArchiveFileChunkRepositoryInterface $driveArchiveFileChunkRepository,
    ) {}

    /**
     * @throws DriveArchiveFileNotFoundException
     * @throws DriveArchiveJobNotFoundException
     * @throws DriveArchiveNotCompletedJobException
     */
    public function handle(int $driveArchiveJobId, int $userId): DriveChunksInfoDTO
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
        return $this->driveArchiveFileChunkRepository->getChunksInfo($driveArchiveFileEntity->getId());
    }
}
