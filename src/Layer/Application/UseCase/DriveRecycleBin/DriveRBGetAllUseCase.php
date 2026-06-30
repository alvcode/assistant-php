<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveRecycleBin;

use App\Layer\Domain\Entity\Aggregate\DriveRecycleBinAggregate;
use App\Layer\Domain\Repository\DriveRecycleBinRepositoryInterface;

final readonly class DriveRBGetAllUseCase
{
    public function __construct(
        private DriveRecycleBinRepositoryInterface $driveRecycleBinRepository,
    ) {}

    /** @return DriveRecycleBinAggregate[] */
    public function handle(int $userId): array
    {
        return $this->driveRecycleBinRepository->getAll($userId);
    }
}
