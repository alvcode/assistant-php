<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Repository\NoteRepositoryInterface;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;

final readonly class NoteRepository implements NoteRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /** @inheritDoc */
    public function checkExistsByCategoryIDs(array $catIDs): bool
    {
        $query = "SELECT EXISTS(SELECT 1 FROM notes WHERE category_id in (:ids))";

        $conn = $this->entityManager->getConnection();
        return (bool) $conn->executeQuery(
            $query,
            ['ids' => $catIDs],
            ['ids' => ArrayParameterType::INTEGER]
        )->fetchOne();
    }
}
