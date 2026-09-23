<?php

declare(strict_types=1);

namespace App\Layer\Domain\Entity;

use App\Layer\Domain\Dict\Drive\DriveArchiveJobStatusEnum;
use App\Layer\Domain\Service\Utils\DateTimeImmutable;

final class DriveArchiveJobEntity
{
    /** @param int[] $structIds */
    public function __construct(
        private ?int $id,
        private int $userId,
        private array $structIds,
        private DriveArchiveJobStatusEnum $status,
        private ?string $errorDescription,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $finishedAt,
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

    /**
     * @return int[]
     */
    public function getStructIds(): array
    {
        return $this->structIds;
    }

    /**
     * @param int[] $structIds
     */
    public function setStructIds(array $structIds): void
    {
        $this->structIds = $structIds;
    }

    public function getStatus(): DriveArchiveJobStatusEnum
    {
        return $this->status;
    }

    public function setFailed(?string $errorDescription): void
    {
        $this->status = DriveArchiveJobStatusEnum::Failed;
        $this->errorDescription = $errorDescription;
    }

    public function setCompleted(): void
    {
        $this->status = DriveArchiveJobStatusEnum::Completed;
    }

    public function setProcessed(): void
    {
        $this->status = DriveArchiveJobStatusEnum::Processed;
    }

    public function setDeleted(): void
    {
        $this->status = DriveArchiveJobStatusEnum::Deleted;
    }

    public function getErrorDescription(): ?string
    {
        return $this->errorDescription;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getFinishedAt(): ?DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function setFinished(): void
    {
        $this->finishedAt = DateTimeImmutable::createNowUtc();
    }
}
