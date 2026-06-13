<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Factory\Drive;

use App\Layer\Domain\Dict\Drive\DriveStructTypeEnum;
use App\Layer\Domain\Entity\DriveStructEntity;
use App\Layer\Domain\Service\Utils\DateTime;
use App\Layer\Domain\Service\Utils\DateTimeImmutable;

final readonly class DriveStructFactory
{
    public function getNewDriveStructDirectory(int $userId, string $name, ?int $parentId): DriveStructEntity
    {
        return new DriveStructEntity(
            id: null,
            userId: $userId,
            name: $name,
            type: DriveStructTypeEnum::Directory,
            parentId: $parentId,
            createdAt: DateTimeImmutable::createNowUtc(),
            updatedAt: DateTime::createNowUtc(),
        );
    }
}