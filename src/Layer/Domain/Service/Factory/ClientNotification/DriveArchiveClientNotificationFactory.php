<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Factory\ClientNotification;

use App\Layer\Domain\Dict\Drive\DriveArchiveJobStatusEnum;
use App\Layer\Domain\ValueObject\ClientNotification\CNArchiveStatusUpdateVO;

final readonly class DriveArchiveClientNotificationFactory
{
    public function getForDeleteArchive(
        int $userId,
        int $jobId,
    ): CNArchiveStatusUpdateVO
    {
        return new CNArchiveStatusUpdateVO(
            userId: $userId,
            jobId: $jobId,
            status: DriveArchiveJobStatusEnum::Deleted->value,
            existsArchive: false,
        );
    }

    public function getForAddToQueueArchive(
        int $userId,
        int $jobId,
    ): CNArchiveStatusUpdateVO
    {
        return new CNArchiveStatusUpdateVO(
            userId: $userId,
            jobId: $jobId,
            status: DriveArchiveJobStatusEnum::New->value,
            existsArchive: true,
        );
    }

    public function getForProcessedArchive(
        int $userId,
        int $jobId,
    ): CNArchiveStatusUpdateVO
    {
        return new CNArchiveStatusUpdateVO(
            userId: $userId,
            jobId: $jobId,
            status: DriveArchiveJobStatusEnum::Processed->value,
            existsArchive: true,
        );
    }

    public function getForCompletedArchive(
        int $userId,
        int $jobId,
    ): CNArchiveStatusUpdateVO
    {
        return new CNArchiveStatusUpdateVO(
            userId: $userId,
            jobId: $jobId,
            status: DriveArchiveJobStatusEnum::Completed->value,
            existsArchive: true,
        );
    }
}
