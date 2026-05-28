<?php

declare(strict_types=1);

namespace App\Layer\Domain\Entity\Aggregate;

use DateTime;
use DateTimeImmutable;

final class NoteListAggregate
{
    public function __construct(
        private ?int $id,
        private int $categoryId,
        private DateTimeImmutable $createdAt,
        private DateTime $updatedAt,
        private ?string $title,
        private bool $pinned,
        private bool $shared,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function setCategoryId(int $categoryId): void
    {
        $this->categoryId = $categoryId;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function isPinned(): bool
    {
        return $this->pinned;
    }

    public function setPinned(bool $pinned): void
    {
        $this->pinned = $pinned;
    }

    public function isShared(): bool
    {
        return $this->shared;
    }

    public function setShared(bool $shared): void
    {
        $this->shared = $shared;
    }
}

