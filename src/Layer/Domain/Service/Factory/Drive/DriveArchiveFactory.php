<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Factory\Drive;

use App\Layer\Domain\Dict\Drive\DriveArchiveJobStatusEnum;
use App\Layer\Domain\Entity\DriveArchiveJobEntity;
use App\Layer\Domain\Service\Utils\DateTimeImmutable;

final readonly class DriveArchiveFactory
{
    /**
     * @param int[] $structIds
     */
    public function getNewDriveArchiveJob(
        int $userId,
        array $structIds,
    ): DriveArchiveJobEntity
    {
        return new DriveArchiveJobEntity(
            id: null,
            userId: $userId,
            structIds: $structIds,
            status: DriveArchiveJobStatusEnum::New,
            errorDescription: null,
            createdAt: DateTimeImmutable::createNowUtc(),
            finishedAt: null,
        );
    }
}
