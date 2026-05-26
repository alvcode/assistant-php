<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Factory\Note;

use App\Layer\Domain\Entity\NoteEntity;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;

final readonly class NoteFactory
{
    public function getNewNote(int $categoryId, ?array $noteBlocks, ?string $title): NoteEntity
    {
        return new NoteEntity(
            id: null,
            categoryId: $categoryId,
            noteBlocks: $noteBlocks,
            createdAt: new DateTimeImmutable('now', new DateTimeZone('UTC')),
            updatedAt: new DateTime('now', new DateTimeZone('UTC')),
            title: $title,
            pinned: false,
        );
    }
}
