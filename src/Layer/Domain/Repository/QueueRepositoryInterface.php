<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

interface QueueRepositoryInterface
{
    public function pushDriveArchive(int $driveArchiveJobId): void;
}
