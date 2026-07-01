<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveRecycleBin;

use App\Layer\Domain\Repository\DriveRecycleBinRepositoryInterface;

final readonly class DriveRBRestoreOneUseCase
{
    public function __construct(
        private DriveRecycleBinRepositoryInterface $driveRecycleBinRepository,
    ) {}

    public function handle(int $id, int $userId): void
    {
        $driveRecycleBinEntity = $this->driveRecycleBinRepository->getById($id);
    }
}
