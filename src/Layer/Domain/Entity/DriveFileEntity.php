<?php

declare(strict_types=1);

namespace App\Layer\Domain\Entity;

use App\Layer\Domain\Service\Utils\DateTimeImmutable;
use App\Layer\Domain\ValueObject\FileSizeVO;

final class DriveFileEntity
{
    public function __construct(
        private ?int $id,
        private int $driveStructId,
        private ?string $path,
        private string $ext,
        private FileSizeVO $size,
        private DateTimeImmutable $createdAt,
        private bool $isChunk,
        private ?string $sha256,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getDriveStructId(): int
    {
        return $this->driveStructId;
    }

    public function setDriveStructId(int $driveStructId): void
    {
        $this->driveStructId = $driveStructId;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    public function getExt(): string
    {
        return $this->ext;
    }

    public function setExt(string $ext): void
    {
        $this->ext = $ext;
    }

    public function getSize(): FileSizeVO
    {
        return $this->size;
    }

    public function setSize(FileSizeVO $size): void
    {
        $this->size = $size;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function isChunk(): bool
    {
        return $this->isChunk;
    }

    public function setIsChunk(bool $isChunk): void
    {
        $this->isChunk = $isChunk;
    }

    public function getSha256(): ?string
    {
        return $this->sha256;
    }

    public function setSha256(?string $sha256): void
    {
        $this->sha256 = $sha256;
    }
}
