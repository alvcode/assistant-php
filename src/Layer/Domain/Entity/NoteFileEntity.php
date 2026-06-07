<?php

declare(strict_types=1);

namespace App\Layer\Domain\Entity;

use App\Layer\Domain\Service\Utils\DateTimeImmutable;
use App\Layer\Domain\ValueObject\FileSizeVO;

final class NoteFileEntity
{
    public function __construct(
        private ?int $id,
        private int $userID,
        private string $originalFilename,
        private string $filePath,
        private string $ext,
        private FileSizeVO $size,
        private string $hash,
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

    public function getUserID(): int
    {
        return $this->userID;
    }

    public function setUserID(int $userID): void
    {
        $this->userID = $userID;
    }

    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): void
    {
        $this->originalFilename = $originalFilename;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
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

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): void
    {
        $this->hash = $hash;
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
