<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Dict\Drive\DriveArchiveJobStatusEnum;
use App\Layer\Domain\Entity\DriveArchiveFileEntity;
use App\Layer\Domain\Entity\DriveArchiveJobEntity;
use App\Layer\Domain\ValueObject\PathVO;

interface DriveArchiveRepositoryInterface
{
    public function save(DriveArchiveJobEntity $entity): DriveArchiveJobEntity;

    public function getJobById(int $id): ?DriveArchiveJobEntity;

    /**
     * @param DriveArchiveJobStatusEnum[] $statuses
     */
    public function existsJobsByUserAndStatuses(int $userId, array $statuses): bool;

    /** @param DriveArchiveJobStatusEnum[] $statuses */
    public function getJobByUserAndStatuses(int $userId, array $statuses): ?DriveArchiveJobEntity;

    public function saveFileEntity(DriveArchiveFileEntity $entity): DriveArchiveFileEntity;

    public function getFileByJobId(int $driveArchiveJobId): ?DriveArchiveFileEntity;

    public function getSaveStructsPath(int $driveArchiveJobId): PathVO;

    public function getSaveArchivePath(int $driveArchiveJobId): PathVO;

    public function getSaveChunksPath(int $driveArchiveJobId): PathVO;
}
