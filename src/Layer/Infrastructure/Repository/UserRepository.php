<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Entity\UserEntity;
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

    public function save(UserEntity $user): UserEntity
    {
        $params = [
            'login' => $user->getLogin(),
            'password' => $user->getPassword(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
        if (is_null($user->getId())) {
            $query = "
                insert into users (login, password, created_at, updated_at)
                values (:login, :password, :created_at, :updated_at) RETURNING id
            ";
        } else {
            $query = "
                update users set login = :login, password = :password, created_at = :created_at
                where id = :id
            ";
            $params['id'] = $user->getId();
        }

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, $params);

        $user->setId($stmt->fetchOne());
        return $user;
    }
}
