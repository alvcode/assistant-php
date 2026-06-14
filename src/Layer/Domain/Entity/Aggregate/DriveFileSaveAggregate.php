<?php

declare(strict_types=1);

namespace App\Layer\Domain\Entity\Aggregate;

use App\Layer\Domain\Entity\DriveFileEntity;
use App\Layer\Domain\Entity\DriveStructEntity;

final class DriveFileSaveAggregate
{
    public function __construct(
        private DriveStructEntity $driveStruct,
        private DriveFileEntity $driveFile, 
    ) {}

    public function getDriveStruct(): DriveStructEntity
    {
        return $this->driveStruct;
    }

    public function setDriveStruct(DriveStructEntity $value): void
    {
        $this->driveStruct = $value;
    }

    public function getDriveFile(): DriveFileEntity
    {
        return $this->driveFile;
    }

    public function setDriveFile(DriveFileEntity $value): void
    {
        $this->driveFile = $value;
    }
}
