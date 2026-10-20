<?php

declare(strict_types=1);

namespace App\Response\Drive;

use App\Layer\Domain\Entity\DriveShareEntity;

final class DriveShareResponse
{
    public function __construct(
        public int $id,
        public int $drive_struct_id,
        public string $hash,
    ) {}

    public static function fromNoteShareEntity(DriveShareEntity $entity): self
    {
        return new self(
            id: $entity->getId(),
            drive_struct_id: $entity->getStructId(),
            hash: $entity->getHash(),
        );
    }
}
