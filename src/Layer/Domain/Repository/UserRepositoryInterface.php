<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Entity\UserEntity;

interface UserRepositoryInterface
{
    public function existsByLogin(string $login): bool;

    public function save(UserEntity $user): UserEntity;
}
