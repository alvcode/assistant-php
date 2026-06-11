<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Infrastructure\DTO\Drive\DriveTreeDTO;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DriveStructRepository implements DriveStructRepositoryInterface
{
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
                createdAt: new \DateTimeImmutable($raw['created_at'], new \DateTimeZone('UTC')),
                updatedAt: new \DateTimeImmutable($raw['updated_at'], new \DateTimeZone('UTC')),
                isChunk: $raw['is_chunk'],
                sha256: $raw['sha256']
            );
        }
        return $result;
    }
}
