<?php

declare(strict_types=1);

namespace App\Response\DriveArchive;

use App\Layer\Domain\Entity\DriveArchiveJobEntity;

final readonly class DriveArchiveJobResponse
{
    public function __construct(
        public bool $existsArchive,
        public ?int $id,
        public ?int $status,
    ) {}

    public static function fromDriveArchiveJobEntity(?DriveArchiveJobEntity $entity): self
    {
        if ($entity === null) {
            return new self(
                existsArchive: false,
                id: null,
                status: null,
            );
        }
        return new self(
            existsArchive: true,
            id: $entity->getId(),
            status: $entity->getStatus()->value,
        );
    }
}
