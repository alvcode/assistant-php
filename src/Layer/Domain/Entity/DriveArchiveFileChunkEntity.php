<?php

declare(strict_types=1);

namespace App\Layer\Domain\Entity;

use App\Layer\Domain\ValueObject\FileSizeVO;
use App\Layer\Domain\ValueObject\PathVO;

final class DriveArchiveFileChunkEntity
{
    public function __construct(
        private ?int $id,
        private int $driveArchiveFileId,
        private PathVO $path,
        private FileSizeVO $size,
        private int $chunkNumber,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getDriveArchiveFileId(): int
    {
        return $this->driveArchiveFileId;
    }

    public function setDriveArchiveFileId(int $driveArchiveFileId): void
    {
        $this->driveArchiveFileId = $driveArchiveFileId;
    }

    public function getPath(): PathVO
    {
        return $this->path;
    }

    public function setPath(PathVO $path): void
    {
        $this->path = $path;
    }

    public function getSize(): FileSizeVO
    {
        return $this->size;
    }

    public function setSize(FileSizeVO $size): void
    {
        $this->size = $size;
    }

    public function getChunkNumber(): int
    {
        return $this->chunkNumber;
    }

    public function setChunkNumber(int $chunkNumber): void
    {
        $this->chunkNumber = $chunkNumber;
    }
}
