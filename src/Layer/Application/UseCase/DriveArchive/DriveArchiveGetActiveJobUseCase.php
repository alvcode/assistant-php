<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveArchive;

use App\Layer\Domain\Dict\Drive\DriveArchiveJobStatusEnum;
use App\Layer\Domain\Entity\DriveArchiveJobEntity;
use App\Layer\Domain\Repository\DriveArchiveRepositoryInterface;

final readonly class DriveArchiveGetActiveJobUseCase
{
    public function __construct(
        private DriveArchiveRepositoryInterface $driveArchiveRepository,
    ) {}

    public function handle(int $userId): ?DriveArchiveJobEntity
    {
        return $this->driveArchiveRepository->getJobByUserAndStatuses(
            userId: $userId,
            statuses: [
                DriveArchiveJobStatusEnum::New,
                DriveArchiveJobStatusEnum::Processed,
                DriveArchiveJobStatusEnum::Completed,
            ]
        );
    }
}
