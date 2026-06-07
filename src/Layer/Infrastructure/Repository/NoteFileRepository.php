<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Dict\Common\FileSizeTypeEnum;
use App\Layer\Domain\Entity\NoteFileEntity;
use App\Layer\Domain\Repository\NoteFileRepositoryInterface;
use App\Layer\Domain\ValueObject\FileSizeVO;
use Doctrine\ORM\EntityManagerInterface;

final readonly class NoteFileRepository implements NoteFileRepositoryInterface
{
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
}
