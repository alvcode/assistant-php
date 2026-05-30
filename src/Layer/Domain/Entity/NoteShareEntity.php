<?php

declare(strict_types=1);

namespace App\Layer\Domain\Entity;

final class NoteShareEntity
{
    public function __construct(
        private ?int $id,
        private int $noteID,
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

    public function getNoteID(): int
    {
        return $this->noteID;
    }

    public function setNoteID(int $noteID): void
    {
        $this->noteID = $noteID;
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
