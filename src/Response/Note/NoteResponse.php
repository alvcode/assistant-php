<?php

declare(strict_types=1);

namespace App\Response\Note;

use App\Infrastructure\FormatDict;
use App\Layer\Domain\Entity\NoteEntity;

final class NoteResponse
{
    public function __construct(
        public int $id,
        public int $category_id,
        public array $note_blocks,
        public string $created_at,
        public string $updated_at,
        public ?string $title,
        public bool $pinned,
    ) {}

    public static function fromNoteEntity(NoteEntity $entity): self
    {
        return new self(
            id: $entity->getId(),
            category_id: $entity->getCategoryId(),
            note_blocks: $entity->getNoteBlocks(),
            created_at: $entity->getCreatedAt()->format(FormatDict::DATETIME_ISO_8601_UTC),
            updated_at: $entity->getUpdatedAt()->format(FormatDict::DATETIME_ISO_8601_UTC),
            title: $entity->getTitle(),
            pinned: $entity->isPinned(),
        );
    }
}
