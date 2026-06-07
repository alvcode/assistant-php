<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository\DTO\Storage;

use App\Layer\Domain\ValueObject\FileSizeVO;

final readonly class SaveFileDTO
{
    public function __construct(
        private \SplFileInfo $file,
        private string $savePath,
        private FileSizeVO $fileSize,
    ) {}

    public function getFile(): \SplFileInfo
    {
        return $this->file;
    }

    public function getSavePath(): string
    {
        return $this->savePath;
    }

    public function getFileSize(): FileSizeVO
    {
        return $this->fileSize;
    }
}
