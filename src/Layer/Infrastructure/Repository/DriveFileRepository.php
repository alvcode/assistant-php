<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Dict\Common\FileSizeTypeEnum;
use App\Layer\Domain\Entity\DriveFileEntity;
use App\Layer\Domain\Repository\DriveFileRepositoryInterface;
use App\Layer\Domain\Service\Utils\DateTimeImmutable;
use App\Layer\Domain\ValueObject\FileSizeVO;
use App\Layer\Infrastructure\Repository\Helper\EachLowCostTrait;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Generator;

final readonly class DriveFileRepository implements DriveFileRepositoryInterface
{
    use EachLowCostTrait;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function getUsedSpaceByUserID(int $userId): FileSizeVO
    {
        $query = "
            SELECT
    		coalesce(sum(df.size), 0) as all_size
            FROM drive_structs ds
            JOIN drive_files df on df.drive_struct_id = ds.id
            WHERE ds.user_id = :user_id
        ";

        $conn = $this->entityManager->getConnection();
        $result = $conn->executeQuery($query, ['user_id' => $userId]);
        $row = $result->fetchAssociative();
        if (!$row) {
            return new FileSizeVO(0, FileSizeTypeEnum::Bytes);
        }

        return new FileSizeVO((float)$row['all_size'], FileSizeTypeEnum::Bytes);
    }

    public function getLastId(): int
    {
        $query = "SELECT coalesce(max(id), 0) FROM drive_files";
        $conn = $this->entityManager->getConnection();
        $result = $conn->executeQuery($query);
        return $result->fetchOne();
    }

    public function save(DriveFileEntity $entity): DriveFileEntity
    {
        $params = [
            'drive_struct_id' => $entity->getDriveStructId(),
            'path' => $entity->getPath(),
            'ext' => $entity->getExt(),
            'size' => $entity->getSize()->getBytes(),
            'created_at' => $entity->getCreatedAt()->format('Y-m-d H:i:s'),
            'is_chunk' => $entity->isChunk(),
            'sha256' => $entity->getSha256()
        ];

        $isNew = is_null($entity->getId());
        if ($isNew) {
            $query = "
                insert into drive_files (drive_struct_id, path, ext, size, created_at, is_chunk, sha256)
                values (:drive_struct_id, :path, :ext, :size, :created_at, :is_chunk, :sha256) RETURNING id
            ";
        } else {
            $query = "
                update drive_files
                set drive_struct_id = :drive_struct_id, path = :path, ext = :ext, size = :size, created_at = :created_at,
                is_chunk = :is_chunk, sha256 = :sha256
                where id = :id
            ";
            $params['id'] = $entity->getId();
        }

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, $params, ['is_chunk' => ParameterType::BOOLEAN]);

        if ($isNew) {
            $entity->setId($stmt->fetchOne());
        }
        return $entity;
    }

    public function getByStructId(int $structId): ?DriveFileEntity
    {
        $query = "select * from drive_files where drive_struct_id = :struct_id";
        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, ['struct_id' => $structId]);

        $row = $stmt->fetchAssociative();
        if (!$row) {
            return null;
        }

        return $this->getEntityFromRaw($row);
    }

    /** @inheritDoc */
    public function getAllRecursive(int $structId, int $userId, bool $includeRecycleBin): array
    {
        if ($includeRecycleBin) {
            $query = "
                select * from drive_files df
                where
                df.drive_struct_id in (
                    WITH RECURSIVE structs AS (
                        SELECT id
                        FROM drive_structs
                        WHERE id = :struct_id and user_id = :user_id

                        UNION ALL

                        SELECT ds.id
                        FROM drive_structs ds
                        INNER JOIN structs s ON ds.parent_id = s.id
                    )
                    SELECT id FROM structs
                )
            ";
        } else {
            $query = "
                select * from drive_files df
                where
                df.drive_struct_id in (
                    WITH RECURSIVE structs AS (
                        SELECT ds1.id
                        FROM drive_structs ds1
                        LEFT JOIN drive_recycle_bin drb1 on drb1.drive_struct_id = ds1.id
                        WHERE drb1.id is null and ds1.id = :struct_id and ds1.user_id = :user_id

                        UNION ALL

                        SELECT ds2.id
                        FROM drive_structs ds2
                        LEFT JOIN drive_recycle_bin drb2 on drb2.drive_struct_id = ds2.id
                        INNER JOIN structs s ON ds2.parent_id = s.id
                        WHERE drb2.id is null
                    )
                    SELECT id FROM structs
                )
            ";
        }

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, ['struct_id' => $structId, 'user_id' => $userId]);
        $result = [];
        foreach ($stmt->fetchAllAssociative() as $raw) {
            $result[] = $this->getEntityFromRaw($raw);
        }
        return $result;
    }

    /** @inheritDoc */
    public function getAll(): Generator
    {
        foreach (
            $this->eachLowCost(
                entityManager: $this->entityManager,
                query: "select * from drive_files",
                where: "",
                params: [],
            ) as $row
        ) {
            yield $this->getEntityFromRaw($row);
        }
    }

    /** @param array<string,mixed> $raw */
    private function getEntityFromRaw(array $raw): DriveFileEntity
    {
        return new DriveFileEntity(
            id: $raw['id'],
            driveStructId: $raw['drive_struct_id'],
            path: $raw['path'],
            ext: $raw['ext'],
            size: new FileSizeVO($raw['size'], FileSizeTypeEnum::Bytes),
            createdAt: DateTimeImmutable::createUTCFromString($raw['created_at']),
            isChunk: $raw['is_chunk'],
            sha256: $raw['sha256']
        );
    }
}
