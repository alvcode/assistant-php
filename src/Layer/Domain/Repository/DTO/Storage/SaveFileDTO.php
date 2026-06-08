<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository\DTO\Storage;

use App\Layer\Domain\ValueObject\FileContentVO;

final readonly class SaveFileDTO
{
    public function __construct(
        private FileContentVO $file,
        private string $savePath,
    ) {}

    public function getFile(): FileContentVO
    {
        return $this->file;
    }

    public function getSavePath(): string
    {
        return $this->savePath;
    }
}
