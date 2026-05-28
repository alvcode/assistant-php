<?php

declare(strict_types=1);

namespace App\Response\Note;

use App\Infrastructure\FormatDict;
use App\Layer\Domain\Entity\Aggregate\NoteListAggregate;

final class NoteListResponse
{
    public function __construct(
        public int $id,
        public ?string $title,
        public int $category_id,
        public bool $shared,
        public string $created_at,
        public string $updated_at,
        public bool $pinned,
    ) {}

    public static function fromNoteListAggregate(NoteListAggregate $entity): self
    {
        return new self(
            id: $entity->getId(),
            title: $entity->getTitle(),
            category_id: $entity->getCategoryId(),
            shared: $entity->isShared(),
            created_at: $entity->getCreatedAt()->format(FormatDict::DATETIME_ISO_8601_UTC),
            updated_at: $entity->getUpdatedAt()->format(FormatDict::DATETIME_ISO_8601_UTC),
            pinned: $entity->isPinned(),
        );
    }

    /**
     * @param NoteListAggregate[] $noteListEntities
     * @return self[]
     */
    public static function fromNoteListAggregates(array $noteListEntities): array
    {
        $result = [];
        foreach ($noteListEntities as $noteListEntity) {
            $result[] = self::fromNoteListAggregate($noteListEntity);
        }
        return $result;
    }
}
