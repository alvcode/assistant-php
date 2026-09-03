<?php

declare(strict_types=1);

namespace App\Layer\Domain\Entity;

use App\Layer\Domain\ValueObject\FileSizeVO;
use DateTimeImmutable;

final class DriveArchiveFileEntity
{
    public function __construct(
        private ?int $id,
        private int $driveArchiveJobId,
        private ?FileSizeVO $size,
        private DateTimeImmutable $createdAt,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getDriveArchiveJobId(): int
    {
        return $this->driveArchiveJobId;
    }

    public function setDriveArchiveJobId(int $driveArchiveJobId): void
    {
        $this->driveArchiveJobId = $driveArchiveJobId;
    }

    public function getSize(): ?FileSizeVO
    {
        return $this->size;
    }

    public function setSize(?FileSizeVO $size): void
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
}
