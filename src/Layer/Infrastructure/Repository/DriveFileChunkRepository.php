<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Dict\Common\FileSizeTypeEnum;
use App\Layer\Domain\Entity\DriveFileChunkEntity;
use App\Layer\Domain\Repository\DriveFileChunkRepositoryInterface;
use App\Layer\Domain\ValueObject\FileSizeVO;
use App\Layer\Infrastructure\DTO\Drive\DriveChunksInfoDTO;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DriveFileChunkRepository implements DriveFileChunkRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /** @inheritDoc */
    public function getAllRecursive(int $structId, int $userId): array
    {
        $query = "
            select * from drive_file_chunks dfc
            where
            dfc.drive_file_id in (
                select df.id from drive_files df
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
            )
        ";
        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, ['struct_id' => $structId, 'user_id' => $userId]);

        $result = [];
        foreach ($stmt->fetchAllAssociative() as $raw) {
            $result[] = $this->getEntityFromRaw($raw);
        }
        return $result;
    }

    public function getChunksSize(int $driveFileId): FileSizeVO
    {
        $query = "
            SELECT
                coalesce(sum(dfc.size), 0)
            FROM drive_file_chunks dfc
            WHERE dfc.drive_file_id = :drive_file_id
        ";

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, ['drive_file_id' => $driveFileId]);
        return new FileSizeVO((float)$stmt->fetchOne(), FileSizeTypeEnum::Bytes);
    }

    public function save(DriveFileChunkEntity $entity): DriveFileChunkEntity
    {
        $params = [
            'drive_file_id' => $entity->getDriveFileId(),
            'path' => $entity->getPath(),
            'size' => $entity->getSize()->getBytes(),
            'chunk_number' => $entity->getChunkNumber(),
        ];

        $isNew = is_null($entity->getId());
        if ($isNew) {
            $query = "
                insert into drive_file_chunks (drive_file_id, path, size, chunk_number)
                values (:drive_file_id, :path, :size, :chunk_number) RETURNING id
            ";
        } else {
            $query = "
                update drive_file_chunks
                set drive_file_id = :drive_file_id, path = :path, size = :size, chunk_number = :chunk_number
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

    public function getChunksInfo(int $fileId): DriveChunksInfoDTO
    {
        $query = "
            select
			(select min(chunk_number) from drive_file_chunks dfc where drive_file_id = :file_id) as min_chunk_number,
			(select max(chunk_number) from drive_file_chunks dfc where drive_file_id = :file_id) as max_chunk_number
        ";

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, ['file_id' => $fileId]);
        $result = $stmt->fetchAssociative();

        return new DriveChunksInfoDTO(
            startNumber: $result['min_chunk_number'],
            endNumber: $result['max_chunk_number']
        );
    }

    public function getByFileIDAndNumber(int $fileId, int $chunkNumber): ?DriveFileChunkEntity
    {
        $query = "
            select * from drive_file_chunks where drive_file_id = :file_id and chunk_number = :chunk_number
        ";
        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, ['file_id' => $fileId, 'chunk_number' => $chunkNumber]);
        $row = $stmt->fetchAssociative();
        if (!$row) {
            return null;
        }
        return $this->getEntityFromRaw($row);
    }

    /** @inheritDoc */
    public function getAllByFileId(int $fileId): array
    {
        $query = "
            select * from drive_file_chunks where drive_file_id = :file_id
        ";
        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, ['file_id' => $fileId]);

        $result = [];
        foreach ($stmt->fetchAllAssociative() as $raw) {
            $result[] = $this->getEntityFromRaw($raw);
        }
        return $result;
    }

    /** @param array<string,mixed> $raw */
    private function getEntityFromRaw(array $raw): DriveFileChunkEntity
    {
        return new DriveFileChunkEntity(
            id: $raw['id'],
            driveFileId: $raw['drive_file_id'],
            path: $raw['path'],
            size: new FileSizeVO($raw['size'], FileSizeTypeEnum::Bytes),
            chunkNumber: $raw['chunk_number']
        );
    }
}
