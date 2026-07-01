<?php

declare(strict_types=1);

namespace App\Layer\Domain\Entity;

use App\Layer\Domain\ValueObject\PathVO;

final class DriveRecycleBinEntity
{
    public function __construct(
        private ?int $id,
        private int $driveStructId,
        private \DateTimeImmutable $createdAt,
        private PathVO $originalPath,
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getOriginalPath(): PathVO
    {
        return $this->originalPath;
    }

    public function setOriginalPath(PathVO $originalPath): void
    {
        $this->originalPath = $originalPath;
    }
}
