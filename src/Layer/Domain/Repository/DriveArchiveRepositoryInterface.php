<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Dict\Drive\DriveArchiveJobStatusEnum;
use App\Layer\Domain\Entity\DriveArchiveJobEntity;

interface DriveArchiveRepositoryInterface
{
    public function save(DriveArchiveJobEntity $entity): DriveArchiveJobEntity;

    public function getJobById(int $id): ?DriveArchiveJobEntity;

    /**
     * @param DriveArchiveJobStatusEnum[] $statuses
     */
    public function existsJobsByUserAndStatuses(int $userId, array $statuses): bool;
}
