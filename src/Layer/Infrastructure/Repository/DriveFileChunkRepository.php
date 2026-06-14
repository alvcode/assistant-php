<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Dict\Common\FileSizeTypeEnum;
use App\Layer\Domain\Entity\DriveFileChunkEntity;
use App\Layer\Domain\Repository\DriveFileChunkRepositoryInterface;
use App\Layer\Domain\ValueObject\FileSizeVO;
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