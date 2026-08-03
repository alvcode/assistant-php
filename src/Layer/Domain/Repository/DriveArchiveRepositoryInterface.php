<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Entity\DriveArchiveJobEntity;

interface DriveArchiveRepositoryInterface
{
    public function save(DriveArchiveJobEntity $entity): DriveArchiveJobEntity;
}
