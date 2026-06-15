<?php

declare(strict_types=1);

namespace App\Layer\Application\DTO\Drive;

use App\Layer\Domain\ValueObject\FileSizeVO;

final readonly class DriveGetFreeSpaceDTO
{
    public function __construct(
        public FileSizeVO $total,
        public FileSizeVO $used,
    ) {}
}