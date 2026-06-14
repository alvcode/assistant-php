<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Drive;

use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileChunkRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Service\Factory\Storage\StorageRepositoryFactoryInterface;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;

final readonly class DriveDeleteStructUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
        private DriveFileRepositoryInterface $driveFileRepository,
        private DriveFileChunkRepositoryInterface $driveFileChunkRepository,
        private ConfigRepositoryInterface $configRepository,
        private FileUtilsInterface $fileUtils,
        private StorageRepositoryFactoryInterface $storageRepositoryFactory,
    ) {}

    /** @throws DriveStructNotFoundException */
    public function handle(int $structId, int $userId): void
    {
        $driveStructEntity = $this->driveStructRepository->getById($structId);
        if (\is_null($driveStructEntity) || $driveStructEntity->getUserId() !== $userId) {
            throw new DriveStructNotFoundException('Структура не найдена');
        }

        $deletePaths = [];
        $baseSavePath = $this->configRepository->getDriveFileSavePath();
        $deleteChunkEntityList = $this->driveFileChunkRepository->getAllRecursive($structId, $userId);
        foreach ($deleteChunkEntityList as $driveFileChunkEntity) {
            $deletePaths[] = $this->fileUtils->pathJoin([$baseSavePath, $driveFileChunkEntity->getPath()]);
        }
        unset($deleteChunkEntityList);

        $deleteFileEntityList = $this->driveFileRepository->getAllRecursive($structId, $userId);
        foreach ($deleteFileEntityList as $driveFileEntity) {
            $deletePaths[] = $this->fileUtils->pathJoin([$baseSavePath, $driveFileEntity->getPath()]);
        }
        unset($deleteFileEntityList);

        if (!empty($deletePaths)) {
            $this->storageRepositoryFactory->getRepository()->deleteAll($deletePaths);
        }

        $this->driveStructRepository->deleteRecursive($structId, $userId);
    }
}