<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveArchive;

use App\Layer\Application\Exception\DriveArchive\DriveArchiveJobNotFoundException;
use App\Layer\Domain\Dict\Common\FileSizeTypeEnum;
use App\Layer\Domain\Entity\DriveArchiveFileChunkEntity;
use App\Layer\Domain\Repository\DriveArchiveFileChunkRepositoryInterface;
use App\Layer\Domain\Repository\DriveArchiveRepositoryInterface;
use App\Layer\Domain\Service\Drive\CreationFolderStructService;
use App\Layer\Domain\Service\Drive\SplitFileIntoChunksService;
use App\Layer\Domain\Service\Factory\Drive\DriveArchiveFactory;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;
use App\Layer\Domain\ValueObject\FileSizeVO;
use Exception;

final readonly class DriveArchiveCreateUseCase
{
    public function __construct(
        private DriveArchiveRepositoryInterface $driveArchiveRepository,
        private DriveArchiveFactory $driveArchiveFactory,
        private FileUtilsInterface $fileUtils,
        private DriveArchiveFileChunkRepositoryInterface $driveArchiveFileChunkRepository,
        private CreationFolderStructService $creationFolderStructService,
        private SplitFileIntoChunksService $splitFileIntoChunksService,
    ) {}

    /**
     * @throws DriveArchiveJobNotFoundException
     * @throws Exception
     */
    public function handle(int $driveArchiveJobId): void
    {
        $driveArchiveJobEntity = $this->driveArchiveRepository->getJobById($driveArchiveJobId);
        if (!$driveArchiveJobEntity) {
            throw new DriveArchiveJobNotFoundException('Archive Job не найден');
        }

        $driveArchiveJobEntity->setProcessed();
        $this->driveArchiveRepository->save($driveArchiveJobEntity);

        // Формируем реальную папку
        $workDirectoryPath = $this->driveArchiveRepository->getSaveStructsPath($driveArchiveJobEntity->getId());
        try {
            $this->creationFolderStructService->handle(
                structIds: $driveArchiveJobEntity->getStructIds(),
                userId: $driveArchiveJobEntity->getUserId(),
                savePath: $workDirectoryPath,
            );
        } catch (Exception $e) {
            $driveArchiveJobEntity->setFailed($e->getMessage());
            $driveArchiveJobEntity->setFinished();
            $this->driveArchiveRepository->save($driveArchiveJobEntity);
            throw $e;
        }

        // формируем из папки архив
        $archivePath = $this->driveArchiveRepository->getSaveArchivePath($driveArchiveJobEntity->getId());
        try {
            $this->fileUtils->createArchive(
                sourcePath: $workDirectoryPath,
                destinationPath: $archivePath
            );

            $driveArchiveFileEntity = $this->driveArchiveRepository->getFileByJobId($driveArchiveJobEntity->getId())
                ?? $this->driveArchiveRepository->saveFileEntity(
                    $this->driveArchiveFactory->getNewDriveArchiveFile($driveArchiveJobEntity->getId())
                );
        } catch (Exception $e) {
            $this->fileUtils->unlinkPath($workDirectoryPath);
            $this->fileUtils->unlinkPath($archivePath);
            $driveArchiveJobEntity->setFailed($e->getMessage());
            $driveArchiveJobEntity->setFinished();
            $this->driveArchiveRepository->save($driveArchiveJobEntity);
            throw $e;
        }

        $this->fileUtils->unlinkPath($workDirectoryPath);

        // разбиваем архив на чанки
        $chunksPath = $this->driveArchiveRepository->getSaveChunksPath($driveArchiveJobEntity->getId());
        $this->fileUtils->unlinkPath($chunksPath);
        $this->driveArchiveFileChunkRepository->deleteByDriveArchiveFileId($driveArchiveFileEntity->getId());

        $totalSize = 0;
        try {
            foreach ($this->splitFileIntoChunksService->handle(
                filePath: $archivePath,
                savePath: $chunksPath,
            ) as $driveFileChunkVO) {
                $this->driveArchiveFileChunkRepository->save(
                    new DriveArchiveFileChunkEntity(
                        id: null,
                        driveArchiveFileId: $driveArchiveFileEntity->getId(),
                        path: $driveFileChunkVO->getPath(),
                        size: $driveFileChunkVO->getSize(),
                        chunkNumber: $driveFileChunkVO->getChunkNumber(),
                    )
                );

                $totalSize += $driveFileChunkVO->getSize()->getBytes();
            }
        } catch (Exception $e) {
            $this->fileUtils->unlinkPath($archivePath);
            $this->fileUtils->unlinkPath($chunksPath);
            $this->driveArchiveFileChunkRepository->deleteByDriveArchiveFileId($driveArchiveFileEntity->getId());
            $driveArchiveJobEntity->setFailed($e->getMessage());
            $driveArchiveJobEntity->setFinished();
            $this->driveArchiveRepository->save($driveArchiveJobEntity);
            throw $e;
        }

        $driveArchiveFileEntity->setSize(new FileSizeVO(size: $totalSize, sizeType: FileSizeTypeEnum::Bytes));
        $this->driveArchiveRepository->saveFileEntity($driveArchiveFileEntity);

        $this->fileUtils->unlinkPath($archivePath);

        $driveArchiveJobEntity->setCompleted();
        $driveArchiveJobEntity->setFinished();
        $this->driveArchiveRepository->save($driveArchiveJobEntity);
    }
}
