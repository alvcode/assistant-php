<?php

declare(strict_types=1);

namespace App\Layer\Domain\Entity;

use App\Layer\Domain\Dict\Drive\DriveStructTypeEnum;
use App\Layer\Domain\Service\Utils\HasherServiceInterface;
use DateTime;
use DateTimeImmutable;

final class DriveStructEntity
{
    public function __construct(
        private ?int $id,
        private int $userId,
        private string $name,
        private DriveStructTypeEnum $type,
        private ?int $parentId,
        private DateTimeImmutable $createdAt,
        private DateTime $updatedAt,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
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

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setParentId(?int $parentId): void
    {
        $this->parentId = $parentId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function generateRestoredName(HasherServiceInterface $hasherService): void
    {
        $this->name .= '_restored_' .$hasherService->generateRandomStringWithoutSymbols(10);
    }
}
