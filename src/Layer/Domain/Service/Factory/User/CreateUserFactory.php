<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Factory\User;

use App\Layer\Domain\Entity\UserEntity;
use App\Layer\Domain\Service\Utils\HasherServiceInterface;
use DateTime;
use DateTimeImmutable;

final readonly class CreateUserFactory
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
}
