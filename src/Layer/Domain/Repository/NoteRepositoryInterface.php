<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

interface NoteRepositoryInterface
{
    /**
     * @param int[] $catIDs
     */
    public function checkExistsByCategoryIDs(array $catIDs): bool;
}
