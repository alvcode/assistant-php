<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Factory\User;

use App\Layer\Domain\Dict\User\UserTokenDictionary;
use App\Layer\Domain\Entity\UserEntity;
use App\Layer\Domain\Entity\UserTokenEntity;
use App\Layer\Domain\Service\Utils\HasherServiceInterface;
use DateTime;
use DateTimeImmutable;

final readonly class UserFactory
{
    public function __construct(
        private HasherServiceInterface $hasherService,
    ) {}

    public function getNewUser(string $login, string $password): UserEntity
    {
        return new UserEntity(
            id: null,
            login: $login,
            password: $this->hasherService->hashUserPassword($password),
            createdAt: new DateTimeImmutable('now', new \DateTimeZone('UTC')),
            updatedAt: new DateTime('now', new \DateTimeZone('UTC'))
        );
    }

    public function getNewToken(int $userId): UserTokenEntity
    {
        return new UserTokenEntity(
            userId: $userId,
            token: $this->hasherService->generateRandomString(UserTokenDictionary::TOKEN_LENGTH),
            refreshToken: $this->hasherService->generateRandomString(UserTokenDictionary::REFRESH_TOKEN_LENGTH),
            expiredTo: time() + (UserTokenDictionary::TOKEN_LIFE_TIME_HOURS * 60 * 60),
        );
    }
}
