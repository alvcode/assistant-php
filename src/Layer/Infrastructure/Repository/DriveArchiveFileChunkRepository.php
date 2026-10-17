<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Dict\Common\FileSizeTypeEnum;
use App\Layer\Domain\Entity\DriveArchiveFileChunkEntity;
use App\Layer\Domain\Repository\DriveArchiveFileChunkRepositoryInterface;
use App\Layer\Domain\Repository\DTO\Drive\DriveChunksInfoDTO;
use App\Layer\Domain\ValueObject\FileSizeVO;
use App\Layer\Domain\ValueObject\PathVO;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DriveArchiveFileChunkRepository implements DriveArchiveFileChunkRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function save(DriveArchiveFileChunkEntity $entity): DriveArchiveFileChunkEntity
    {
        $params = [
            'drive_archive_file_id' => $entity->getDriveArchiveFileId(),
            'path' => $entity->getPath()->getPath(),
            'size' => $entity->getSize()->getBytes(),
            'chunk_number' => $entity->getChunkNumber(),
        ];

        $isNew = is_null($entity->getId());
        if ($isNew) {
            $query = "
                insert into drive_archive_file_chunks (drive_archive_file_id, path, size, chunk_number)
                values (:drive_archive_file_id, :path, :size, :chunk_number) RETURNING id
            ";
        } else {
            $query = "
                update drive_archive_file_chunks
                set drive_archive_file_id = :drive_archive_file_id, path = :path, size = :size, chunk_number = :chunk_number
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

    public function deleteByDriveArchiveFileId(int $driveArchiveFileId): void
    {
        $conn = $this->entityManager->getConnection();
        $conn->executeQuery(
            "delete from drive_archive_file_chunks where drive_archive_file_id = :drive_archive_file_id",
            ['drive_archive_file_id' => $driveArchiveFileId]
        );
    }

    public function getChunksInfo(int $driveArchiveFileId): DriveChunksInfoDTO
    {
        $query = "
            select
			(
			    select min(chunk_number) from drive_archive_file_chunks dafc
                where dafc.drive_archive_file_id = :file_id
            ) as min_chunk_number,
			(
			    select max(chunk_number) from drive_archive_file_chunks dafc
                where dafc.drive_archive_file_id = :file_id
            ) as max_chunk_number
        ";

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, ['file_id' => $driveArchiveFileId]);
        $result = $stmt->fetchAssociative();

        return new DriveChunksInfoDTO(
            startNumber: $result['min_chunk_number'],
            endNumber: $result['max_chunk_number']
        );
    }

    public function getByFileIdAndChunkNumber(int $fileId, int $chunkNumber): ?DriveArchiveFileChunkEntity
    {
        $query = "
            select * from drive_archive_file_chunks where drive_archive_file_id = :file_id and chunk_number = :chunk_number
        ";
        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, ['file_id' => $fileId, 'chunk_number' => $chunkNumber]);
        $row = $stmt->fetchAssociative();
        if (!$row) {
            return null;
        }
        return $this->getEntityFromRaw($row);
    }

    /** @param array<string,mixed> $raw */
    private function getEntityFromRaw(array $raw): DriveArchiveFileChunkEntity
    {
        return new DriveArchiveFileChunkEntity(
            id: $raw['id'],
            driveArchiveFileId: $raw['drive_archive_file_id'],
            path: new PathVO($raw['path']),
            size: new FileSizeVO($raw['size'], FileSizeTypeEnum::Bytes),
            chunkNumber: $raw['chunk_number']
        );
    }
}
