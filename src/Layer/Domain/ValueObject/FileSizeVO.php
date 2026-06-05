<?php

declare(strict_types=1);

namespace App\Layer\Domain\ValueObject;

use App\Layer\Domain\Dict\Common\FileSizeTypeEnum;

final readonly class FileSizeVO
{
    private int $bytes;

    public function __construct(float $size, FileSizeTypeEnum $sizeType)
    {
        $this->bytes = (int) round(match ($sizeType) {
            FileSizeTypeEnum::Bytes => $size,
            FileSizeTypeEnum::Kilobytes => $size * 1024,
            FileSizeTypeEnum::Megabytes => $size * 1024 ** 2,
            FileSizeTypeEnum::Gigabytes => $size * 1024 ** 3,
        });
    }

    public function getBytes(): int
    {
        return $this->bytes;
    }

    public function getKilobytes(): float
    {
        return $this->bytes / 1024;
    }

    public function getMegabytes(): float
    {
        return $this->bytes / (1024 ** 2);
    }

    public function getGigabytes(): float
    {
        return $this->bytes / (1024 ** 3);
    }
}
