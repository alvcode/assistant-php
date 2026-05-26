<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Entity\NoteEntity;
use App\Layer\Domain\Repository\NoteRepositoryInterface;
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
}
