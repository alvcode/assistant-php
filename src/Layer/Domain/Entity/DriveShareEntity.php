<?php

declare(strict_types=1);

namespace App\Layer\Domain\Entity;

final class DriveShareEntity
{
    public function __construct(
        private ?int $id,
        private int $structId,
        private string $hash,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getStructId(): int
    {
        return $this->structId;
    }

    public function setStructId(int $structId): void
    {
        $this->structId = $structId;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }
}
