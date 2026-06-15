<?php

declare(strict_types=1);

namespace App\Response\Drive;

use App\Layer\Application\DTO\Drive\DriveGetFreeSpaceDTO;

final readonly class DriveGetFreeSpaceResponse
{
    public function __construct(
        public int $total,
        public int $used,
    ) {}

    public static function fromDriveGetFreeSpaceDTO(DriveGetFreeSpaceDTO $dto): self 
    {
        return new self(
            total: $dto->total->getBytes(),
            used: $dto->used->getBytes(),
        );
    }
}