<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Repository\FileNoteLinkRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class FileNoteLinkRepository implements FileNoteLinkRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /** @inheritDoc */
    public function upsert(int $noteID, array $fileIDs): void
    {
        $conn = $this->entityManager->getConnection();
        $conn->beginTransaction();

        try {
            $this->deleteByNoteID($noteID);

            if (!empty($fileIDs)) {
                $this->add($noteID, $fileIDs);
            }

            $conn->commit();
        } catch (\Throwable $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    public function deleteByNoteID(int $noteID): void
    {
        $query = "DELETE FROM file_note_links WHERE note_id = :note_id";
        $conn = $this->entityManager->getConnection();
        $conn->executeQuery($query, ['note_id' => $noteID]);
    }

    /** @inheritDoc */
    public function add(int $noteID, array $fileIDs): void
    {
        $conn = $this->entityManager->getConnection();
        foreach ($fileIDs as $fileID) {
            $query = "INSERT INTO file_note_links (file_id, note_id) VALUES (:file_id, :note_id)";
            $conn->executeQuery($query, ['file_id' => $fileID, 'note_id' => $noteID]);
        }
    }
}
