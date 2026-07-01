<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Dict\Drive\DriveStructTypeEnum;
use App\Layer\Domain\Entity\Aggregate\DriveRecycleBinAggregate;
use App\Layer\Domain\Entity\DriveRecycleBinEntity;
use App\Layer\Domain\Repository\DriveRecycleBinRepositoryInterface;
use App\Layer\Domain\ValueObject\PathVO;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DriveRecycleBinRepository implements DriveRecycleBinRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function upsert(int $structId, string $originalPath, DateTimeImmutable $createdAt): void
    {
        $query = "
            INSERT INTO drive_recycle_bin (drive_struct_id, created_at, original_path)
            VALUES (:struct_id, :created_at, :original_path)
            ON CONFLICT (drive_struct_id) DO NOTHING
        ";

        $conn = $this->entityManager->getConnection();
        $conn->executeQuery(
            $query,
            [
                'struct_id' => $structId,
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
                'original_path' => $originalPath,
            ]
        );
    }

    public function deleteAllChildren(int $parentStructId, int $userId): void
    {
        $query = "
            delete from drive_recycle_bin drb
            where
            drb.drive_struct_id in (
                WITH RECURSIVE structs AS (
                    SELECT *
                    FROM drive_structs
                    WHERE id = :parent_struct_id and user_id = :user_id

                    UNION ALL

                    SELECT ds.*
                    FROM drive_structs ds
                    INNER JOIN structs s ON ds.parent_id = s.id
                )
                SELECT id FROM structs where id != :parent_struct_id
            )
        ";
        $conn = $this->entityManager->getConnection();
        $conn->executeQuery($query, ['parent_struct_id' => $parentStructId, 'user_id' => $userId]);
    }

    /** @inheritDoc */
    public function getAll(int $userId): array
    {
        $query = "
            select
                drb.id, ds.name, ds.type, drb.drive_struct_id, drb.created_at, drb.original_path
            from drive_recycle_bin drb
            join drive_structs ds on ds.id = drb.drive_struct_id
            where
            ds.user_id = :user_id
        ";

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, ['user_id' => $userId]);

        $result = [];
        foreach ($stmt->fetchAllAssociative() as $raw) {
            $result[] = new DriveRecycleBinAggregate(
                id: $raw['id'],
                name: $raw['name'],
                type: DriveStructTypeEnum::tryFrom($raw['type']),
                driveStructId: $raw['drive_struct_id'],
                createdAt: \App\Layer\Domain\Service\Utils\DateTimeImmutable::createUTCFromString($raw['created_at']),
                originalPath: $raw['original_path'],
            );
        }
        return $result;
    }

    public function getById(int $id): ?DriveRecycleBinEntity
    {
        $query = "
            select * from drive_recycle_bin where id = :id
        ";

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, ['id' => $id]);
        $row = $stmt->fetchAssociative();
        if (!$row) {
            return null;
        }

        return new DriveRecycleBinEntity(
            id: $row['id'],
            driveStructId: $row['drive_struct_id'],
            createdAt: \App\Layer\Domain\Service\Utils\DateTimeImmutable::createUTCFromString($row['created_at']),
            originalPath: new PathVO($row['original_path']),
        );
    }
}
