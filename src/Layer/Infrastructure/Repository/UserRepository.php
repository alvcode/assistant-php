<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function existsByLogin(string $login): bool
    {
        $query = "
            select 1 from users u where u.login = :login
        ";
        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, ['login' => $login]);
        return $stmt->fetchOne() !== false;
    }
}
