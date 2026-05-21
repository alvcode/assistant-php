<?php

declare(strict_types=1);

namespace App\Layer\Application\DTO\NoteCategory;

final readonly class CreateNoteCategoryDTO
{
    public function __construct(
        public int $userId,
        public string $name,
        public ?int $parentId,
    ) {}
}
