<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveRecycleBin;

use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Application\Exception\DriveRecycleBin\DriveRecycleBinNotFoundException;
use App\Layer\Application\Service\TransactionManagerInterface;
use App\Layer\Domain\Exception\Storage\FailedStorageConfigurationException;
use App\Layer\Domain\Repository\DriveRecycleBinRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Service\Drive\DriveDeletedStructPathsService;
use App\Layer\Domain\Service\Factory\Storage\StorageRepositoryFactoryInterface;

final readonly class DriveRBForceDeleteOneUseCase
{
    public function __construct(
        private DriveRecycleBinRepositoryInterface $driveRecycleBinRepository,
        private DriveStructRepositoryInterface $driveStructRepository,
        private DriveDeletedStructPathsService $driveDeletedStructPathsService,
        private StorageRepositoryFactoryInterface $storageRepositoryFactory,
        private TransactionManagerInterface $transactionManager,
    ) {}

    /**
     * @throws DriveRecycleBinNotFoundException
     * @throws DriveStructNotFoundException
     * @throws FailedStorageConfigurationException
     */
    public function handle(int $recycleBinId, int $userId): void
    {
        $recycleBinEntity = $this->driveRecycleBinRepository->getById($recycleBinId);
        if (is_null($recycleBinEntity)) {
            throw new DriveRecycleBinNotFoundException('Запись корзины не найдена');
        }

        $driveStructEntity = $this->driveStructRepository->getById(
            $recycleBinEntity->getDriveStructId(),
            true
        );

        if (is_null($driveStructEntity) || $driveStructEntity->getUserId() !== $userId) {
            throw new DriveStructNotFoundException('Удаляемая структура не найдена');
        }

        $deletePaths = $this->driveDeletedStructPathsService->getPathsForDelete($driveStructEntity->getId(), $userId);
        if (!empty($deletePaths)) {
            $this->storageRepositoryFactory->getRepository()->deleteAll($deletePaths);
        }

        $this->transactionManager->transactional(function () use ($driveStructEntity, $recycleBinEntity, $userId) {
            $this->driveStructRepository->deleteRecursiveWithRecycleBin($driveStructEntity->getId(), $userId);
            $this->driveRecycleBinRepository->delete($recycleBinEntity);
        });
    }
}
