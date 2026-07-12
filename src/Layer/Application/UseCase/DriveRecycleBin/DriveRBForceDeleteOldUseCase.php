<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveRecycleBin;

use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Application\Exception\DriveRecycleBin\DriveRecycleBinNotFoundException;
use App\Layer\Domain\Exception\Storage\FailedStorageConfigurationException;
use App\Layer\Domain\Repository\DriveRecycleBinRepositoryInterface;
use DateTimeImmutable;

final readonly class DriveRBForceDeleteOldUseCase
{
    public function __construct(
        private DriveRecycleBinRepositoryInterface $driveRecycleBinRepository,
        private DriveRBForceDeleteOneUseCase $driveRBForceDeleteOneUseCase,
    ) {}

    /**
     * @throws DriveRecycleBinNotFoundException
     * @throws DriveStructNotFoundException
     * @throws FailedStorageConfigurationException
     */
    public function handle(DateTimeImmutable $deleteOlderThen): void
    {
        foreach ($this->driveRecycleBinRepository->getAllOutdatedIterator($deleteOlderThen) as $driveRecycleBinAggregate) {
            $this->driveRBForceDeleteOneUseCase->handle(
                $driveRecycleBinAggregate->getId(),
                $driveRecycleBinAggregate->getUserId()
            );
        }
    }
}
