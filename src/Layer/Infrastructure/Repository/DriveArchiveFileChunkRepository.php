<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Entity\DriveArchiveFileChunkEntity;
use App\Layer\Domain\Repository\DriveArchiveFileChunkRepositoryInterface;
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
            'path' => $entity->getPath(),
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
}
