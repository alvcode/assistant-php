<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Entity\Aggregate\NoteListAggregate;
use App\Layer\Domain\Entity\NoteEntity;

interface NoteRepositoryInterface
{
    /**
     * @param int[] $catIDs
     */
    public function checkExistsByCategoryIDs(array $catIDs): bool;

    public function getByID(int $id): ?NoteEntity;

    public function save(NoteEntity $entity): NoteEntity;

    /**
     * @param int[] $categoryIDs
     * @return NoteListAggregate[]
     */
    public function getListByCategoryIds(array $categoryIDs): array;
}
