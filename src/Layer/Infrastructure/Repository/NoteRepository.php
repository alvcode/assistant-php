<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Entity\Aggregate\NoteListAggregate;
use App\Layer\Domain\Entity\NoteEntity;
use App\Layer\Domain\Repository\NoteRepositoryInterface;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;

final readonly class NoteRepository implements NoteRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /** @inheritDoc */
    public function checkExistsByCategoryIDs(array $catIDs): bool
    {
        $query = "SELECT EXISTS(SELECT 1 FROM notes WHERE category_id in (:ids))";

        $conn = $this->entityManager->getConnection();
        return (bool) $conn->executeQuery(
            $query,
            ['ids' => $catIDs],
            ['ids' => ArrayParameterType::INTEGER]
        )->fetchOne();
    }

    public function getByID(int $id): ?NoteEntity
    {
        $query = "select * from notes where id = :id";
        $conn = $this->entityManager->getConnection();
        $result = $conn->executeQuery($query, ['id' => $id]);

        $row = $result->fetchAssociative();
        if (!$row) {
            return null;
        }
        return $this->getEntityFromRaw($row);
    }

    public function save(NoteEntity $entity): NoteEntity
    {
        $params = [
            'category_id' => $entity->getCategoryId(),
            'note_blocks' => json_encode($entity->getNoteBlocks()),
            'created_at' => $entity->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $entity->getUpdatedAt()->format('Y-m-d H:i:s'),
            'title' => $entity->getTitle(),
            'pinned' => $entity->isPinned(),
        ];

        $isNew = is_null($entity->getId());
        if ($isNew) {
            $query = "
                insert into notes (category_id, note_blocks, created_at, updated_at, title, pinned)
                values (:category_id, :note_blocks, :created_at, :updated_at, :title, :pinned) RETURNING id
            ";
        } else {
            $query = "
                update notes
                set category_id = :category_id, note_blocks = :note_blocks, created_at = :created_at,
                    updated_at = :updated_at, title = :title, pinned = :pinned
                where id = :id
            ";
            $params['id'] = $entity->getId();
        }

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, $params, ['pinned' => ParameterType::BOOLEAN]);

        if ($isNew) {
            $entity->setId($stmt->fetchOne());
        }
        return $entity;
    }

    public function delete(NoteEntity $entity): void
    {
        $query = "DELETE FROM notes WHERE id = :id";
        $conn = $this->entityManager->getConnection();
        $conn->executeQuery($query, ['id' => $entity->getId()]);
    }

    /** @inheritDoc */
    public function getListByCategoryIds(array $categoryIDs): array
    {
        $query = "
            select
                n.id,
                n.category_id,
                n.created_at,
                n.updated_at,
                n.title,
                n.pinned,
                (SELECT EXISTS(SELECT 1 FROM note_share_hashes WHERE note_id = n.id)) as shared
            from notes n
            where n.category_id in (:ids)
        ";

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, ['ids' => $categoryIDs], ['ids' => ArrayParameterType::INTEGER]);

        $result = [];
        foreach ($stmt->fetchAllAssociative() as $raw) {
            $result[] = new NoteListAggregate(
                id: $raw['id'],
                categoryId: $raw['category_id'],
                createdAt: new DateTimeImmutable($raw['created_at'], new DateTimeZone('UTC')),
                updatedAt: new DateTime($raw['updated_at'], new DateTimeZone('UTC')),
                title: $raw['title'],
                pinned: $raw['pinned'],
                shared: $raw['shared']
            );
        }
        return $result;
    }

    public function getByShareHash(string $hash): ?NoteEntity
    {
        $query = "select * from notes where id = (select nsh.note_id from note_share_hashes nsh where nsh.hash = :hash)";
        $conn = $this->entityManager->getConnection();
        $result = $conn->executeQuery($query, ['hash' => $hash]);

        $row = $result->fetchAssociative();
        if (!$row) {
            return null;
        }
        return $this->getEntityFromRaw($row);
    }

    public function isBelongToUser(int $noteID, int $userID): bool
    {
        $query = "
            select EXISTS(
                select 1 from note_categories nc
                left join notes n on n.category_id = nc.id
                where
                n.id = :note_id and nc.user_id = :user_id
            )
        ";

        $conn = $this->entityManager->getConnection();
        return (bool) $conn->executeQuery(
            $query,
            ['note_id' => $noteID, 'user_id' => $userID],
        )->fetchOne();
    }

    /** @param array<string,mixed> $raw */
    private function getEntityFromRaw(array $raw): NoteEntity
    {
        return new NoteEntity(
            id: $raw['id'],
            categoryId: $raw['category_id'],
            noteBlocks: json_decode($raw['note_blocks'], true, 512, JSON_THROW_ON_ERROR),
            createdAt: new DateTimeImmutable($raw['created_at'], new DateTimeZone('UTC')),
            updatedAt: new DateTime($raw['updated_at'], new DateTimeZone('UTC')),
            title: $raw['title'],
            pinned: $raw['pinned'],
        );
    }
}
