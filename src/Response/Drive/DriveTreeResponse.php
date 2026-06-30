<?php

declare(strict_types=1);

namespace App\Response\Drive;

use App\Infrastructure\FormatDict;
use App\Layer\Domain\Repository\DTO\Drive\DriveTreeDTO;

final class DriveTreeResponse
{
    public function __construct(
        public int $id,
        public int $user_id,
        public string $name,
        public int $type,
        public int $size,
        public string $created_at,
        public string $updated_at,
        public bool $is_chunk,
        public ?string $sha256,
    ) {}

    public static function fromDriveTreeDTO(DriveTreeDTO $driveTreeDTO): self
    {
        return new self(
            id: $driveTreeDTO->id,
            user_id: $driveTreeDTO->userId,
            name: $driveTreeDTO->name,
            type: $driveTreeDTO->type,
            size: $driveTreeDTO->size,
            created_at: $driveTreeDTO->createdAt->format(FormatDict::DATETIME_ISO_8601_UTC),
            updated_at: $driveTreeDTO->updatedAt->format(FormatDict::DATETIME_ISO_8601_UTC),
            is_chunk: $driveTreeDTO->isChunk,
            sha256: $driveTreeDTO->sha256,
        );
    }

    /**
     * @param DriveTreeDTO[] $DTOs
     * @return self[]
     */
    public static function fromDriveTreeDTOs(array $DTOs): array
    {
        $result = [];
        foreach ($DTOs as $dto) {
            $result[] = self::fromDriveTreeDTO($dto);
        }
        return $result;
    }
}
