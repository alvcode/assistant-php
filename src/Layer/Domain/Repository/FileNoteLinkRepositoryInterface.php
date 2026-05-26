<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

interface FileNoteLinkRepositoryInterface
{
    /**
     * @param int[] $fileIDs
     */
    public function upsert(int $noteID, array $fileIDs): void;

    public function deleteByNoteID(int $noteID): void;

    /**
     * @param int[] $fileIDs
     */
    public function add(int $noteID, array $fileIDs): void;
}
