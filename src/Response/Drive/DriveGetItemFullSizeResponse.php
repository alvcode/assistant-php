<?php

declare(strict_types=1);

namespace App\Response\Drive;

use App\Layer\Domain\ValueObject\FileSizeVO;

final readonly class DriveGetItemFullSizeResponse
{
    public function __construct(
        public int $bytes,
    ) {}

    public static function fromFileSizeVO(FileSizeVO $in): self
    {
        return new self(
            bytes: $in->getBytes(),
        );
    }
}
