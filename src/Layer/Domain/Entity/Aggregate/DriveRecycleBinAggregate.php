<?php

declare(strict_types=1);

namespace App\Layer\Domain\Entity\Aggregate;

use App\Layer\Domain\Dict\Drive\DriveStructTypeEnum;
use DateTimeImmutable;

final class DriveRecycleBinAggregate
{
    public function __construct(
        private int $id,
        private string $name,
        private DriveStructTypeEnum $type,
        private int $driveStructId,
        private DateTimeImmutable $createdAt,
        private string $originalPath,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): DriveStructTypeEnum
    {
        return $this->type;
    }

    public function setType(DriveStructTypeEnum $type): void
    {
        $this->type = $type;
    }

    public function getDriveStructId(): int
    {
        return $this->driveStructId;
    }

    public function setDriveStructId(int $driveStructId): void
    {
        $this->driveStructId = $driveStructId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getOriginalPath(): string
    {
        return $this->originalPath;
    }

    public function setOriginalPath(string $originalPath): void
    {
        $this->originalPath = $originalPath;
    }
}
