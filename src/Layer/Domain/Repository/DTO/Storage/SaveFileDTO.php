<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository\DTO\Storage;

use SplFileInfo;

final readonly class SaveFileDTO
{
    public function __construct(
        private SplFileInfo $file,
        private string $savePath,
    ) {}

    public function getFile(): SplFileInfo
    {
        return $this->file;
    }

    public function getSavePath(): string
    {
        return $this->savePath;
    }
}
