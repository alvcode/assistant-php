<?php

declare(strict_types=1);

namespace App\Layer\Application\DTO\NoteCategory;

final readonly class UpdateNoteCategoryDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?int $parentId,
    ) {}
}
