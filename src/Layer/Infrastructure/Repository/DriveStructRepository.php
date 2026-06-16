<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Dict\Drive\DriveStructTypeEnum;
use App\Layer\Domain\Entity\DriveStructEntity;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Service\Utils\DateTime;
use App\Layer\Domain\Service\Utils\DateTimeImmutable;
use App\Layer\Infrastructure\DTO\Drive\DriveTreeDTO;
use App\Layer\Infrastructure\Repository\Helper\ArrayHelperTrait;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DriveStructRepository implements DriveStructRepositoryInterface
{
    use ArrayHelperTrait;
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /** @inheritDoc */
    public function getTreeByUserID(int $userID, ?int $parentID): array
    {
        $query = sprintf(
            "%s %s",
            "
                select
                    ds.id, ds.user_id, ds.name, ds.type, ds.created_at, ds.updated_at,
                    coalesce(df.size, 0) as size,
                    coalesce(df.is_chunk, false) as is_chunk,
                    df.sha256
                from drive_structs ds
                left join drive_files df on ds.id = df.drive_struct_id
                where user_id = :user_id and
            ",
            $parentID ? "parent_id = :parent_id" : "parent_id is null"
        );
        $params = ['user_id' => $userID];
        if ($parentID) {
            $params['parent_id'] = $parentID;
        }

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, $params);

        $result = [];
        foreach ($stmt->fetchAllAssociative() as $raw) {
            $result[] = new DriveTreeDTO(
                id: $raw['id'],
                userId: $raw['user_id'],
                name: $raw['name'],
                type: $raw['type'],
                size: $raw['size'],
                createdAt: DateTimeImmutable::createUTCFromString($raw['created_at']),
                updatedAt: DateTimeImmutable::createUTCFromString($raw['updated_at']),
                isChunk: $raw['is_chunk'],
                sha256: $raw['sha256']
            );
        }
        return $result;
    }

    public function findRow(
        int $userId,
        string $name,
        DriveStructTypeEnum $type,
        ?int $parentId = null,
    ): ?DriveStructEntity
    {
        $query = sprintf(
            "%s %s",
            "SELECT * FROM drive_structs WHERE user_id = :user_id AND name = :name AND type = :type AND",
            is_null($parentId) ? 'parent_id IS NULL' : 'parent_id = :parent_id'
        );

        $params = [
            'user_id' => $userId,
            'name' => $name,
            'type' => $type->value,
        ];
        if (!is_null($parentId)) {
            $params['parent_id'] = $parentId;
        }

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, $params);

        $row = $stmt->fetchAssociative();
        if (!$row) {
            return null;
        }

        return $this->getEntityFromRaw($row);
    }

    public function save(DriveStructEntity $entity): DriveStructEntity
    {
        $params = [
            'user_id' => $entity->getUserId(),
            'name' => $entity->getName(),
            'type' => $entity->getType()->value,
            'parent_id' => $entity->getParentId(),
            'created_at' => $entity->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $entity->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];

        $isNew = is_null($entity->getId());
        if ($isNew) {
            $query = "
                insert into drive_structs (user_id, name, type, parent_id, created_at, updated_at)
                values (:user_id, :name, :type, :parent_id, :created_at, :updated_at) RETURNING id
            ";
        } else {
            $query = "
                update drive_structs
                set user_id = :user_id, name = :name, type = :type, parent_id = :parent_id, created_at = :created_at, 
                updated_at = :updated_at
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

    public function getById(int $id): ?DriveStructEntity
    {
        $query = "SELECT * FROM drive_structs WHERE id = :id";
        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, ['id' => $id]);

        $row = $stmt->fetchAssociative();
        if (!$row) {
            return null;
        }

        return $this->getEntityFromRaw($row);
    }

    public function deleteRecursive(int $structId, int $userId): void 
    {
        $query = "
            DELETE FROM drive_structs
            WHERE id in (
                WITH RECURSIVE structs AS (
                    SELECT *
                    FROM drive_structs 
                    WHERE id = :struct_id and user_id = :user_id
                
                    UNION ALL
                
                    SELECT ds.*
                    FROM drive_structs ds
                    INNER JOIN structs s ON ds.parent_id = s.id
                )
                SELECT id FROM structs
            )
        ";
        $conn = $this->entityManager->getConnection();
        $conn->executeQuery($query, ['struct_id' => $structId, 'user_id' => $userId]);
    }

    /** @inheritDoc */
    public function structCountByUserAndIds(int $userId, array $structIds): int
    {
        $conn = $this->entityManager->getConnection();
        $result = 0;
        foreach ($this->arrayChunk($structIds, 200) as $batch) {
            $query = "
                select 
                    coalesce(count(ds.id), 0) as count
                from drive_structs ds 
                where user_id = :user_id and id in (:struct_ids)
            ";
            $stmt = $conn->executeQuery(
                $query, 
                ['user_id' => $userId, 'struct_ids' => $batch], 
                ['struct_ids' => ArrayParameterType::INTEGER]
            );

            $result += $stmt->fetchOne();
        }
        
        return $result;
    }

    /** @inheritDoc */
    public function massUpdateParentId(?int $parentId, array $structIds): void 
    {
        $conn = $this->entityManager->getConnection();

        foreach ($this->arrayChunk($structIds, 200) as $batch) {
            $query = "UPDATE drive_structs SET parent_id = :parent_id WHERE id in (:struct_ids)";
            $conn->executeQuery(
                $query, 
                ['parent_id' => $parentId, 'struct_ids' => $batch], 
                ['struct_ids' => ArrayParameterType::INTEGER]
            );
        }
    }

    /** @param array<string,mixed> $raw */
    private function getEntityFromRaw(array $raw): DriveStructEntity
    {
        return new DriveStructEntity(
            id: $raw['id'],
            userId: $raw['user_id'],
            name: $raw['name'],
            type: DriveStructTypeEnum::tryFrom($raw['type']),
            parentId: $raw['parent_id'],
            createdAt: DateTimeImmutable::createUTCFromString($raw['created_at']),
            updatedAt: DateTime::createUTCFromString($raw['updated_at']),
        );
    }
}
