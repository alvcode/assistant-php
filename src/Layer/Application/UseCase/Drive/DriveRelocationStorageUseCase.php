<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Drive;

use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileChunkRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileRepositoryInterface;
use App\Layer\Domain\Repository\DTO\Storage\SaveFileDTO;
use App\Layer\Domain\Service\Factory\Storage\StorageRepositoryFactoryInterface;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;

final readonly class DriveRelocationStorageUseCase
{
    public function __construct(
        private DriveFileRepositoryInterface $driveFileRepository,
        private DriveFileChunkRepositoryInterface $driveFileChunkRepository,
        private StorageRepositoryFactoryInterface $storageRepositoryFactory,
        private ConfigRepositoryInterface $configRepository,
        private FileUtilsInterface $fileUtils,
    ) {}

    public function handle(bool $toLocal): void
    {
        foreach ($this->driveFileRepository->getAll() as $driveFileEntity) {
            if ($driveFileEntity->isChunk()) {
                $driveFileChunks = $this->driveFileChunkRepository->getAllByFileId($driveFileEntity->getId());
                foreach ($driveFileChunks as $driveFileChunk) {
                    $fullFilePath = $this->fileUtils->pathJoin([
                        $this->configRepository->getDriveFileSavePath(),
                        $driveFileChunk->getPath()
                    ]);
                    $toLocal ? $this->toLocal($fullFilePath) : $this->toS3($fullFilePath);
                }
            } else {
                $fullFilePath = $this->fileUtils->pathJoin([
                    $this->configRepository->getDriveFileSavePath(),
                    $driveFileEntity->getPath()
                ]);
                $toLocal ? $this->toLocal($fullFilePath) : $this->toS3($fullFilePath);
            }
        }
    }

    private function toLocal(string $fullPath): void
    {
        $localStorageRepository = $this->storageRepositoryFactory->getLocalStorage();
        $s3StorageRepository = $this->storageRepositoryFactory->getS3Storage();
        if ($localStorageRepository->isExists($fullPath)) {
            return;
        }
        $oldFile = $s3StorageRepository->getFile($fullPath);
        $localStorageRepository->save(new SaveFileDTO(file: $oldFile, savePath: $fullPath));
        $s3StorageRepository->delete($fullPath);
    }

    private function toS3(string $fullPath): void
    {
        $localStorageRepository = $this->storageRepositoryFactory->getLocalStorage();
        $s3StorageRepository = $this->storageRepositoryFactory->getS3Storage();
        if ($s3StorageRepository->isExists($fullPath)) {
            return;
        }
        $oldFile = $localStorageRepository->getFile($fullPath);
        $s3StorageRepository->save(new SaveFileDTO(file: $oldFile, savePath: $fullPath));
        $localStorageRepository->delete($fullPath);
    }
}
