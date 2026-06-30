<?php

declare(strict_types=1);

namespace App\Response\DriveRecycleBin;

use App\Infrastructure\FormatDict;
use App\Layer\Domain\Entity\Aggregate\DriveRecycleBinAggregate;

final readonly class DriveRBGetAllResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public int $type,
        public int $drive_struct_id,
        public string $created_at,
        public string $original_path,
    ) {}

    /**
     * @param DriveRecycleBinAggregate[] $aggregates
     * @return self[]
     */
    public static function fromDriveRBAggregates(array $aggregates): array
    {
        $result = [];
        foreach ($aggregates as $aggregate) {
            $result[] = new self(
                id: $aggregate->getId(),
                name: $aggregate->getName(),
                type: $aggregate->getType()->value,
                drive_struct_id: $aggregate->getDriveStructId(),
                created_at: $aggregate->getCreatedAt()->format(FormatDict::DATETIME_ISO_8601_UTC),
                original_path: $aggregate->getOriginalPath(),
            );
        }
        return $result;
    }
}

