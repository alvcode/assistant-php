<?php

declare(strict_types=1);

namespace App\Layer\Domain\Entity;

use App\Layer\Domain\ValueObject\FileSizeVO;

final class DriveFileChunkEntity
{
    public function __construct(
        private ?int $id,
        private int $driveFileId,
        private string $path,
        private FileSizeVO $size,
        private int $chunkNumber,
    ) {}

    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getDriveFileId(): int
    {
        return $this->driveFileId;
    }

    public function setDriveFileId(int $driveFileId): void
    {
        $this->driveFileId = $driveFileId;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
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