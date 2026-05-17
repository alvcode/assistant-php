<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Entity\UserEntity;
use App\Layer\Domain\Repository\UserRepositoryInterface;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
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

    public function getByToken(string $token, int $unixTime): ?UserEntity
    {
        $conn = $this->entityManager->getConnection();

        $sql = '
            SELECT u.*
            FROM users u
            INNER JOIN user_tokens ut ON u.id = ut.user_id
            WHERE ut.token = :token and ut.expired_to > :expired_to
        ';

        $result = $conn->executeQuery($sql, [
            'token' => $token,
            'expired_to' => $unixTime,
        ]);

        $row = $result->fetchAssociative();
        if (!$row) {
            return null;
        }

        return $this->getEntityFromRaw($row);
    }

    public function getByLogin(string $login): ?UserEntity
    {
        $query = "
            select * from users u where u.login = :login
        ";

        $conn = $this->entityManager->getConnection();
        $result = $conn->executeQuery($query, [
            'login' => $login,
        ]);

        $row = $result->fetchAssociative();
        if (!$row) {
            return null;
        }

        return $this->getEntityFromRaw($row);
    }

    private function getEntityFromRaw(array $raw): UserEntity
    {
        return new UserEntity(
            id: $raw['id'],
            login: $raw['login'],
            password: $raw['password'],
            createdAt: new DateTimeImmutable($raw['created_at'], new DateTimeZone('UTC')),
            updatedAt: new DateTime($raw['updated_at'], new DateTimeZone('UTC')),
        );
    }
}
