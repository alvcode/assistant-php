<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

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
}
