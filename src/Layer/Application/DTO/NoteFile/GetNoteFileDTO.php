<?php

declare(strict_types=1);

namespace App\Layer\Application\DTO\NoteFile;

final readonly class GetNoteFileDTO
{
    public function __construct(
        public \SplFileInfo $file,
        public string $originalFileName,
    ) {}
}
