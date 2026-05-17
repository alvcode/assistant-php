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

    public function save(UserTokenEntity $userToken): UserTokenEntity
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
}
