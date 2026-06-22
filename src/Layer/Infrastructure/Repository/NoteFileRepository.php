<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Dict\Common\FileSizeTypeEnum;
use App\Layer\Domain\Entity\NoteFileEntity;
use App\Layer\Domain\Repository\NoteFileRepositoryInterface;
use App\Layer\Domain\Service\Utils\DateTimeImmutable;
use App\Layer\Domain\ValueObject\FileSizeVO;
use App\Layer\Infrastructure\Repository\Helper\EachLowCostTrait;
use DateTimeZone;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Generator;

final readonly class NoteFileRepository implements NoteFileRepositoryInterface
{
    use EachLowCostTrait;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function getUsedSpaceByUserID(int $userID): FileSizeVO
    {
        $query = "SELECT coalesce(sum(size), 0) as all_size FROM files where user_id = :user_id";

        $conn = $this->entityManager->getConnection();
        $result = $conn->executeQuery($query, ['user_id' => $userID]);
        $row = $result->fetchAssociative();
        if (!$row) {
            return new FileSizeVO(0, FileSizeTypeEnum::Bytes);
        }

        return new FileSizeVO((float)$row['all_size'], FileSizeTypeEnum::Bytes);
    }

    public function getLastID(): int
    {
        $query = "SELECT coalesce(max(id), 0) FROM files";
        $conn = $this->entityManager->getConnection();
        $result = $conn->executeQuery($query);
        return $result->fetchOne();
    }

    public function save(NoteFileEntity $entity): NoteFileEntity
    {
        $params = [
            'user_id' => $entity->getUserID(),
            'original_filename' => $entity->getOriginalFilename(),
            'file_path' => $entity->getFilePath(),
            'ext' => $entity->getExt(),
            'size' => $entity->getSize()->getBytes(),
            'hash' => $entity->getHash(),
            'created_at' => $entity->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        $isNew = is_null($entity->getId());
        if ($isNew) {
            $query = "
                insert into files (user_id, original_filename, file_path, ext, size, hash, created_at)
                values (:user_id, :original_filename, :file_path, :ext, :size, :hash, :created_at) RETURNING id
            ";
        } else {
            $query = "
                update notes
                set user_id = :user_id, original_filename = :original_filename, file_path = :file_path,
                    ext = :ext, size = :size, hash = :hash, created_at = :created_at
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

    public function getByHash(string $hash): ?NoteFileEntity
    {
        $query = "select * from files where hash = :hash";
        $conn = $this->entityManager->getConnection();
        $result = $conn->executeQuery($query, ['hash' => $hash]);

        $row = $result->fetchAssociative();
        if (!$row) {
            return null;
        }
        return $this->getEntityFromRaw($row);
    }

    public function getById(int $id): ?NoteFileEntity
    {
        $query = "select * from files where id = :id";
        $conn = $this->entityManager->getConnection();
        $result = $conn->executeQuery($query, ['id' => $id]);

        $row = $result->fetchAssociative();
        if (!$row) {
            return null;
        }
        return $this->getEntityFromRaw($row);
    }

    /** @inheritDoc */
    public function getAllFiles(): Generator
    {
        foreach (
            $this->eachLowCost(
                entityManager: $this->entityManager,
                query: "select * from files",
                where: "",
                params: [],
            ) as $row
        ) {
            yield $this->getEntityFromRaw($row);
        }
    }

    /** @inheritDoc */
    public function getCountByUserAndIDs(int $userID, array $fileIDs): int
    {
        $query = "SELECT coalesce(count(id), 0) FROM files where user_id = :user_id and id in (:file_ids)";
        $conn = $this->entityManager->getConnection();
        return $conn->executeQuery(
            $query,
            ['user_id' => $userID, 'file_ids' => $fileIDs],
            ['file_ids' => ArrayParameterType::INTEGER]
        )->fetchOne();
    }

    public function delete(NoteFileEntity $entity): void
    {
        $query = "DELETE FROM files WHERE id = :id";
        $conn = $this->entityManager->getConnection();
        $conn->executeQuery($query, ['id' => $entity->getId()]);
    }

    /** @param array<string,mixed> $raw */
    private function getEntityFromRaw(array $raw): NoteFileEntity
    {
        return new NoteFileEntity(
            id: $raw['id'],
            userID: $raw['user_id'],
            originalFilename: $raw['original_filename'],
            filePath: $raw['file_path'],
            ext: $raw['ext'],
            size: new FileSizeVO(size: $raw['size'], sizeType: FileSizeTypeEnum::Bytes),
            hash: $raw['hash'],
            createdAt: new DateTimeImmutable($raw['created_at'], new DateTimeZone('UTC')),
        );
    }
}
