<?php

declare(strict_types=1);

namespace App\Layer\Domain\Entity;

use DateTime;
use DateTimeImmutable;

final class NoteEntity
{
    public function __construct(
        private ?int $id,
        private int $categoryId,
        private array $noteBlocks,
        private DateTimeImmutable $createdAt,
        private DateTime $updatedAt,
        private ?string $title,
        private bool $pinned,
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

    public function getNoteBlocks(): array
    {
        return $this->noteBlocks;
    }

    public function setNoteBlocks(array $noteBlocks): void
    {
        $this->noteBlocks = $noteBlocks;
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

    /**
     * TODO: протестировать, когда будет реализована загрузка файлов
     * @return int[]
     */
    public function getAttachedFileIDs(): array
    {
        $result = [];
        foreach ($this->noteBlocks as $noteBlock) {
            if (isset($noteBlock['type']) && ($noteBlock['type'] === 'attaches' || $noteBlock['type'] === 'image')) {
                $fileID = $noteBlock['data']['file']['id'];
                if ($fileID) {
                    $result[] = (int)$fileID;
                }
            }
        }
        return $result;
    }
}
