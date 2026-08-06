<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Dict\Drive\DriveArchiveJobStatusEnum;
use App\Layer\Domain\Entity\DriveArchiveJobEntity;
use App\Layer\Domain\Repository\DriveArchiveRepositoryInterface;
use App\Layer\Domain\Service\Utils\DateTimeImmutable;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DriveArchiveRepository implements DriveArchiveRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function save(DriveArchiveJobEntity $entity): DriveArchiveJobEntity
    {
        $params = [
            'user_id' => $entity->getUserId(),
            'struct_ids' => json_encode($entity->getStructIds(), JSON_THROW_ON_ERROR),
            'status' => $entity->getStatus()->value,
            'error_description' => $entity->getErrorDescription(),
            'created_at' => $entity->getCreatedAt()->format('Y-m-d H:i:s'),
            'finished_at' => $entity->getFinishedAt()?->format('Y-m-d H:i:s'),
        ];

        $isNew = is_null($entity->getId());
        if ($isNew) {
            $query = "
                insert into drive_archive_jobs (user_id, struct_ids, status, error_description, created_at, finished_at)
                values (:user_id, :struct_ids, :status, :error_description, :created_at, :finished_at) RETURNING id
            ";
        } else {
            $query = "
                update drive_archive_jobs
                set user_id = :user_id, struct_ids = :struct_ids, status = :status, error_description = :error_description,
                created_at = :created_at, finished_at = :finished_at
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

    public function getJobById(int $id): ?DriveArchiveJobEntity
    {
        $query = "select * from drive_archive_jobs where id = :id";
        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, ['id' => $id]);

        $row = $stmt->fetchAssociative();
        if (!$row) {
            return null;
        }

        return $this->getJobEntityFromRaw($row);
    }

    /** @inheritDoc */
    public function existsJobsByUserAndStatuses(int $userId, array $statuses): bool
    {
        $query = "
            select EXISTS(
                select 1 from drive_archive_jobs daj
                where
                daj.user_id = :user_id and daj.status in (:statuses)
            )
        ";

        $conn = $this->entityManager->getConnection();
        return (bool)$conn->executeQuery(
            $query,
            [
                'user_id' => $userId,
                'statuses' => array_map(static fn(DriveArchiveJobStatusEnum $status) => $status->value, $statuses)
            ],
            ['statuses' => ArrayParameterType::INTEGER]
        )->fetchOne();
    }

    /** @paaram array<string,mixed> $ */
    private function getJobEntityFromRaw(array $raw): DriveArchiveJobEntity
    {
        return new DriveArchiveJobEntity(
            id: $raw['id'],
            userId: $raw['user_id'],
            structIds: json_decode($raw['struct_ids'], false, 10, JSON_THROW_ON_ERROR),
            status: DriveArchiveJobStatusEnum::from($raw['status']),
            errorDescription: $raw['error_description'],
            createdAt: DateTimeImmutable::createUTCFromString($raw['created_at']),
            finishedAt: DateTimeImmutable::createUTCFromString($raw['finished_at']),
        );
    }
}
