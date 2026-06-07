<?php

declare(strict_types=1);

namespace App\Response\NoteFile;

use App\Infrastructure\FormatDict;
use App\Layer\Domain\Entity\NoteFileEntity;

final class UploadNoteFileResponse
{
    public function __construct(
        public int $id,
        public string $original_filename,
        public string $ext,
        public int $size_bytes,
        public string $url,
        public string $created_at,
    ) {}

    public static function fromNoteFileEntity(NoteFileEntity $entity, string $downloadBaseUrl): self
    {
        return new self(
            id: $entity->getId(),
            original_filename: $entity->getOriginalFilename(),
            ext: $entity->getExt(),
            size_bytes: $entity->getSize()->getBytes(),
            url: $downloadBaseUrl . '/' . $entity->getHash(),
            created_at: $entity->getCreatedAt()->format(FormatDict::DATETIME_ISO_8601_UTC),
        );
    }
}
