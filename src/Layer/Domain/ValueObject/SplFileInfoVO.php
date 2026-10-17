<?php

declare(strict_types=1);

namespace App\Layer\Domain\ValueObject;

final class SplFileInfoVO
{
    public function __construct(
        private \SplFileInfo $file,
        private bool $isTemporary,
    ) {}

    public function getFile(): \SplFileInfo
    {
        return $this->file;
    }

    public function isTemporary(): bool
    {
        return $this->isTemporary;
    }

    public function unlinkIfTemporary(): void
    {
        if ($this->isTemporary() && file_exists($this->file->getPathname())) {
            unlink($this->file->getPathname());
        }
    }
}
