<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Entity\UserTokenEntity;
use App\Layer\Domain\Repository\UserTokenRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class UserTokenRepository implements UserTokenRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function create(UserTokenEntity $userToken): UserTokenEntity
    {
        $query = "
            insert into user_tokens (user_id, token, refresh_token, expired_to)
            values (:user_id, :token, :refresh_token, :expired_to)
        ";

        $conn = $this->entityManager->getConnection();
        $conn->executeQuery($query, [
            'user_id' => $userToken->getUserId(),
            'token' => $userToken->getToken(),
            'refresh_token' => $userToken->getRefreshToken(),
            'expired_to' => $userToken->getExpiredTo(),
        ]);

        return $userToken;
    }

    public function getByTokenAndRefreshToken(string $token, string $refreshToken): ?UserTokenEntity
    {
        $query = "
            select * from user_tokens where token = :token and refresh_token = :refresh_token
        ";

        $conn = $this->entityManager->getConnection();
        $result = $conn->executeQuery($query, [
            'token' => $token,
            'refresh_token' => $refreshToken,
        ]);

        $row = $result->fetchAssociative();
        if (!$row) {
            return null;
        }

        return $this->getEntityFromRaw($row);
    }

    /** @param array<string,mixed> $row */
    private function getEntityFromRaw(array $row): UserTokenEntity
    {
        return new UserTokenEntity(
            userId: $row['user_id'],
            token: $row['token'],
            refreshToken: $row['refresh_token'],
            expiredTo: $row['expired_to'],
        );
    }
}
