<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Dict\Drive\DriveStructTypeEnum;
use App\Layer\Domain\Entity\Aggregate\DriveRecycleBinAggregate;
use App\Layer\Domain\Repository\DriveRecycleBinRepositoryInterface;
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
}
