<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository\DTO\Storage;

use App\Layer\Domain\ValueObject\PathVO;
use SplFileInfo;

final readonly class SaveFileDTO
{
    public function __construct(
        private SplFileInfo $file,
        private PathVO $savePath,
    ) {}

    public function getFile(): SplFileInfo
    {
        return $this->file;
    }

    public function getSavePath(): PathVO
    {
        return $this->savePath;
    }
}
