<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveRecycleBin;

use App\Layer\Application\Exception\DriveRecycleBin\DriveRecycleBinNotFoundException;
use App\Layer\Domain\Repository\DriveRecycleBinRepositoryInterface;

final readonly class DriveRBRestoreAllUseCase
{
    public function __construct(
        private DriveRecycleBinRepositoryInterface $driveRecycleBinRepository,
        private DriveRBRestoreOneUseCase $driveRBRestoreOneUseCase,
    ) {}

    /**
     * @throws DriveRecycleBinNotFoundException
     */
    public function handle(int $userId): void
    {
        foreach ($this->driveRecycleBinRepository->getAll($userId) as $driveRecycleBinAggregate) {
            $this->driveRBRestoreOneUseCase->handle($driveRecycleBinAggregate->getId(), $userId);
        }
    }
}
