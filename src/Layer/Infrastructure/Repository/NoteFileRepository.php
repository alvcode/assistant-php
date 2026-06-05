<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Dict\Common\FileSizeTypeEnum;
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
}
