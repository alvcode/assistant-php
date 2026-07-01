<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Drive;

use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Application\Service\TransactionManagerInterface;
use App\Layer\Domain\Exception\Storage\FailedStorageConfigurationException;
use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileChunkRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileRepositoryInterface;
use App\Layer\Domain\Repository\DriveRecycleBinRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Service\Factory\Storage\StorageRepositoryFactoryInterface;
use App\Layer\Domain\Service\Utils\DateTimeImmutable;
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
        private DriveRecycleBinRepositoryInterface $driveRecycleBinRepository,
        private TransactionManagerInterface $transactionManager,
    ) {}

    /** @throws DriveStructNotFoundException
     * @throws FailedStorageConfigurationException
     */
    public function handle(int $structId, int $userId, bool $force): void
    {
        $driveStructEntity = $this->driveStructRepository->getById($structId, false);
        if (\is_null($driveStructEntity) || $driveStructEntity->getUserId() !== $userId) {
            throw new DriveStructNotFoundException('Структура не найдена');
        }

        if ($force) {
            $deletePaths = [];
            $baseSavePath = $this->configRepository->getDriveFileSavePath();
            $deleteChunkEntityList = $this->driveFileChunkRepository->getAllRecursive(
                $structId,
                $userId,
                false
            );
            foreach ($deleteChunkEntityList as $driveFileChunkEntity) {
                $deletePaths[] = $this->fileUtils->pathJoin([$baseSavePath, $driveFileChunkEntity->getPath()]);
            }
            unset($deleteChunkEntityList);

            $deleteFileEntityList = $this->driveFileRepository->getAllRecursive(
                $structId,
                $userId,
                false
            );
            foreach ($deleteFileEntityList as $driveFileEntity) {
                if (!$driveFileEntity->isChunk()) {
                    $deletePaths[] = $this->fileUtils->pathJoin([$baseSavePath, $driveFileEntity->getPath()]);
                }
            }
            unset($deleteFileEntityList);

            if (!empty($deletePaths)) {
                $this->storageRepositoryFactory->getRepository()->deleteAll($deletePaths);
            }

            $this->driveStructRepository->deleteRecursiveWithoutRecycleBin($structId, $userId);
        } else {
            $originalPath = '/';
            $nestedDriveStructEntities = $this->driveStructRepository->getAllRecursiveBackward($structId, $userId);

            $nestedDriveStructEntities = array_reverse($nestedDriveStructEntities);
            foreach ($nestedDriveStructEntities as $nestedStructEntity) {
                if ($nestedStructEntity->getId() === $structId) {
                    continue;
                }
                $originalPath = sprintf("%s%s/", $originalPath, $nestedStructEntity->getName());
            }

            $this->transactionManager->transactional(function () use ($structId, $userId, $originalPath) {
                $this->driveRecycleBinRepository->deleteAllChildren($structId, $userId);
                $this->driveRecycleBinRepository->upsert($structId, $originalPath, DateTimeImmutable::createNowUtc());
            });
        }
    }
}
