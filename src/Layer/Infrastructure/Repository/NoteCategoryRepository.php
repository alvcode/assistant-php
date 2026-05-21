<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Entity\NoteCategoryEntity;
use App\Layer\Domain\Repository\NoteCategoryRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class NoteCategoryRepository implements NoteCategoryRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function save(NoteCategoryEntity $entity): NoteCategoryEntity
    {
        $params = [
            'user_id' => $entity->getUserId(),
            'name' => $entity->getName(),
            'parent_id' => $entity->getParentId(),
            'position' => $entity->getPosition(),
        ];

        $isNew = is_null($entity->getId());
        if ($isNew) {
            $query = "
                insert into note_categories (user_id, name, parent_id, position)
                values (:user_id, :name, :parent_id, :position) RETURNING id
            ";
        } else {
            $query = "
                update note_categories
                set user_id = :user_id, name = :name, parent_id = :parent_id, position = :position
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

    public function getById(int $id): ?NoteCategoryEntity
    {
        $conn = $this->entityManager->getConnection();

        $sql = '
            SELECT *
            FROM note_categories
            WHERE id = :id
        ';

        $result = $conn->executeQuery($sql, ['id' => $id]);

        $row = $result->fetchAssociative();
        if (!$row) {
            return null;
        }

        return $this->getEntityFromRaw($row);
    }

    private function getEntityFromRaw(array $row): NoteCategoryEntity
    {
        return new NoteCategoryEntity(
            id: $row['id'],
            userId: $row['user_id'],
            name: $row['name'],
            parentId: $row['parent_id'],
            position: $row['position'],
        );
    }
}
