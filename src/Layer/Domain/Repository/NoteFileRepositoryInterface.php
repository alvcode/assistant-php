<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Entity\NoteFileEntity;
use App\Layer\Domain\ValueObject\FileSizeVO;
use Generator;

interface NoteFileRepositoryInterface
{
    public function getUsedSpaceByUserID(int $userID): FileSizeVO;

    public function getLastID(): int;

    public function save(NoteFileEntity $entity): NoteFileEntity;

    public function getByHash(string $hash): ?NoteFileEntity;

    public function getById(int $id): ?NoteFileEntity;

    /**
     * @return Generator<int,NoteFileEntity>
     */
    public function getAllFiles(): Generator;

    public function delete(NoteFileEntity $entity): void;

    /** @param int[] $fileIDs */
    public function getCountByUserAndIDs(int $userID, array $fileIDs): int;
}
