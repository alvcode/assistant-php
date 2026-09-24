<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveArchive;

use App\Layer\Application\Exception\DriveArchive\DriveArchiveJobNotFoundException;
use App\Layer\Application\Exception\DriveArchive\DriveArchiveNotCompletedJobException;
use App\Layer\Application\Exception\DriveArchive\DriveArchiveRemoveException;
use App\Layer\Domain\Dict\Drive\DriveArchiveJobStatusEnum;
use App\Layer\Domain\Repository\DriveArchiveRepositoryInterface;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;
use Exception;

final readonly class DriveArchiveRemoveUseCase
{
    public function __construct(
        private DriveArchiveRepositoryInterface $driveArchiveRepository,
        private FileUtilsInterface $fileUtils,
    ) {}

    /**
     * @throws DriveArchiveJobNotFoundException
     * @throws Exception
     */
    public function handle(int $driveArchiveJobId, int $userId): void
    {
        $driveArchiveJobEntity = $this->driveArchiveRepository->getJobById($driveArchiveJobId);
        if (!$driveArchiveJobEntity || $driveArchiveJobEntity->getUserId() !== $userId) {
            throw new DriveArchiveJobNotFoundException('Archive Job не найден');
        }
        if ($driveArchiveJobEntity->getStatus() !== DriveArchiveJobStatusEnum::Completed) {
            throw new DriveArchiveNotCompletedJobException('Нельзя удалить архив в незавершенном статусе');
        }

        $chunksPath = $this->driveArchiveRepository->getSaveChunksPath($driveArchiveJobEntity->getId());
        $this->fileUtils->unlinkPath($chunksPath);
        if ($this->fileUtils->isPathExists($chunksPath)) {
            throw new DriveArchiveRemoveException('Не удалось удалить папку с архивом');
        }

        $driveArchiveJobEntity->setDeleted();
        $this->driveArchiveRepository->save($driveArchiveJobEntity);
    }
}
