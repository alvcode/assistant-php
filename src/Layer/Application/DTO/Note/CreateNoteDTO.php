<?php

declare(strict_types=1);

namespace App\Layer\Application\DTO\Note;

final readonly class CreateNoteDTO
{
    public function __construct(
        public int $categoryId,
        public ?string $title,
        public array $noteBlocks,
    ) {}
}
