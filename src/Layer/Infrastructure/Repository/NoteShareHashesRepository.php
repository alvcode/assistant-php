<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Entity\NoteShareEntity;
use App\Layer\Domain\Repository\NoteShareHashesRepositoryInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;

final readonly class NoteShareHashesRepository implements NoteShareHashesRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function existsByNoteID(int $noteID): bool
    {
        $query = "SELECT EXISTS(SELECT 1 FROM note_share_hashes WHERE note_id = :note_id)";
        $conn = $this->entityManager->getConnection();
        return (bool) $conn->executeQuery(
            $query,
            ['note_id' => $noteID],
        )->fetchOne();
    }

    public function getByNoteID(int $noteID): ?NoteShareEntity
    {
        $query = "select * from note_share_hashes where note_id = :note_id";
        $conn = $this->entityManager->getConnection();
        $result = $conn->executeQuery($query, ['note_id' => $noteID]);

        $row = $result->fetchAssociative();
        if (!$row) {
            return null;
        }
        return new NoteShareEntity(
            id: $row['id'],
            noteID: $row['note_id'],
            hash: $row['hash'],
        );
    }

    public function existsByHash(string $hash): bool
    {
        $query = "SELECT EXISTS(SELECT 1 FROM note_share_hashes WHERE hash = :hash)";
        $conn = $this->entityManager->getConnection();
        return (bool) $conn->executeQuery(
            $query,
            ['hash' => $hash],
        )->fetchOne();
    }

    public function save(NoteShareEntity $entity): NoteShareEntity
    {
        $params = [
            'note_id' => $entity->getNoteID(),
            'hash' => $entity->getHash(),
        ];

        $isNew = is_null($entity->getId());
        if ($isNew) {
            $query = "
                insert into note_share_hashes (note_id, hash)
                values (:note_id, :hash) RETURNING id
            ";
        } else {
            $query = "
                update note_share_hashes
                set note_id = :note_id, hash = :hash
                where id = :id
            ";
            $params['id'] = $entity->getId();
        }

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, $params);

        if ($isNew) {
            $entity->setId($stmt->fetchOne());
        }
        return $entity;
    }

    public function deleteByNoteID(int $noteID): void
    {
        $query = "DELETE FROM note_share_hashes WHERE note_id = :note_id";
        $conn = $this->entityManager->getConnection();
        $conn->executeQuery($query, ['note_id' => $noteID]);
    }
}
