<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Entity\NoteShareEntity;

interface NoteShareHashesRepositoryInterface
{
    public function existsByNoteID(int $noteID): bool;

    public function getByNoteID(int $noteID): ?NoteShareEntity;

    public function existsByHash(string $hash): bool;

    public function save(NoteShareEntity $entity): NoteShareEntity;
}
