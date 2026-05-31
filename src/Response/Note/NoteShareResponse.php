<?php

declare(strict_types=1);

namespace App\Response\Note;

use App\Layer\Domain\Entity\NoteShareEntity;

final class NoteShareResponse
{
    public function __construct(
        public int $id,
        public int $note_id,
        public string $hash,
    ) {}

    public static function fromNoteShareEntity(NoteShareEntity $entity): self
    {
        return new self(
            id: $entity->getId(),
            note_id: $entity->getNoteID(),
            hash: $entity->getHash(),
        );
    }
}
