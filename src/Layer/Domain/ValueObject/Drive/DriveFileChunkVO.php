<?php

declare(strict_types=1);

namespace App\Layer\Domain\ValueObject\Drive;

use App\Layer\Domain\ValueObject\FileSizeVO;
use App\Layer\Domain\ValueObject\PathVO;

final readonly class DriveFileChunkVO
{
    public function __construct(
        private PathVO $path,
        private FileSizeVO $size,
        private int $chunkNumber,
    ) {}

    public function getPath(): PathVO
    {
        return $this->path;
    }

    public function getSize(): FileSizeVO
    {
        return $this->size;
    }

    public function getChunkNumber(): int
    {
        return $this->chunkNumber;
    }
}
