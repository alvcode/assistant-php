<?php

declare(strict_types=1);

namespace App\Layer\Application\DTO\Note;

final readonly class UpdateNoteDTO
{
    /** @param array<int,array<string,mixed>> $noteBlocks */
    public function __construct(
        public int $id,
        public int $categoryId,
        public ?string $title,
        public array $noteBlocks,
    ) {}
}
