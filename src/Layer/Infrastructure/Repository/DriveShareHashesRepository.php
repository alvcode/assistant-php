<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Entity\DriveShareEntity;
use App\Layer\Domain\Repository\DriveShareHashesRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DriveShareHashesRepository implements DriveShareHashesRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function existsByStructId(int $structId): bool
    {
        $query = "SELECT EXISTS(SELECT 1 FROM drive_share_hashes WHERE drive_struct_id = :struct_id)";
        $conn = $this->entityManager->getConnection();
        return (bool) $conn->executeQuery(
            $query,
            ['struct_id' => $structId],
        )->fetchOne();
    }

    public function getByStructId(int $structId): ?DriveShareEntity
    {
        $query = "select * from drive_share_hashes where drive_struct_id = :struct_id";
        $conn = $this->entityManager->getConnection();
        $result = $conn->executeQuery($query, ['struct_id' => $structId]);

        $row = $result->fetchAssociative();
        if (!$row) {
            return null;
        }

        return $this->getEntityFromRaw($row);
    }

    public function existsByHash(string $hash): bool
    {
        $query = "SELECT EXISTS(SELECT 1 FROM drive_share_hashes WHERE hash = :hash)";
        $conn = $this->entityManager->getConnection();
        return (bool) $conn->executeQuery(
            $query,
            ['hash' => $hash],
        )->fetchOne();
    }

    public function getByHash(string $hash): ?DriveShareEntity
    {
        $query = "select * from drive_share_hashes where hash = :hash";
        $conn = $this->entityManager->getConnection();
        $result = $conn->executeQuery($query, ['hash' => $hash]);

        $row = $result->fetchAssociative();
        if (!$row) {
            return null;
        }

        return $this->getEntityFromRaw($row);
    }

    public function save(DriveShareEntity $entity): DriveShareEntity
    {
        $params = [
            'drive_struct_id' => $entity->getStructId(),
            'hash' => $entity->getHash(),
        ];

        $isNew = is_null($entity->getId());
        if ($isNew) {
            $query = "
                insert into drive_share_hashes (drive_struct_id, hash)
                values (:drive_struct_id, :hash) RETURNING id
            ";
        } else {
            $query = "
                update drive_share_hashes
                set drive_struct_id = :drive_struct_id, hash = :hash
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

    public function deleteByStructId(int $structId): void
    {
        $query = "DELETE FROM drive_share_hashes WHERE drive_struct_id = :drive_struct_id";
        $conn = $this->entityManager->getConnection();
        $conn->executeQuery($query, ['drive_struct_id' => $structId]);
    }

    private function getEntityFromRaw(array $raw): DriveShareEntity
    {
        return new DriveShareEntity(
            id: $raw['id'],
            structId: $raw['drive_struct_id'],
            hash: $raw['hash'],
        );
    }
}
