<?php

declare(strict_types=1);

namespace App\Layer\Application\DTO\NoteFile;

use App\Layer\Domain\ValueObject\FileContentVO;

final readonly class GetNoteFileDTO
{
    public function __construct(
        public FileContentVO $fileContent,
        public string $originalFileName,
    ) {}
}
