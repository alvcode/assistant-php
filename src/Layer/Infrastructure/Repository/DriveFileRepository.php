<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Dict\Common\FileSizeTypeEnum;
use App\Layer\Domain\Entity\DriveFileEntity;
use App\Layer\Domain\Repository\DriveFileRepositoryInterface;
use App\Layer\Domain\Service\Utils\DateTimeImmutable;
use App\Layer\Domain\ValueObject\FileSizeVO;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DriveFileRepository implements DriveFileRepositoryInterface
{
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