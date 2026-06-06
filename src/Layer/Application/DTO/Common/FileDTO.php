<?php

declare(strict_types=1);

namespace App\Layer\Application\DTO\Common;

use SplFileInfo;

final readonly class FileDTO
{
    public function __construct(
        private SplFileInfo $file,
        private string $originalExtension,
    ) {}

    public function getFile(): SplFileInfo
    {
        return $this->file;
    }

    public function getOriginalExtension(): string
    {
        return $this->originalExtension;
    }
}
