<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Entity\NoteFileEntity;
use App\Layer\Domain\ValueObject\FileSizeVO;

interface NoteFileRepositoryInterface
{
    public function getUsedSpaceByUserID(int $userID): FileSizeVO;

    public function getLastID(): int;

    public function save(NoteFileEntity $entity): NoteFileEntity;

    public function getByHash(string $hash): ?NoteFileEntity;
}
