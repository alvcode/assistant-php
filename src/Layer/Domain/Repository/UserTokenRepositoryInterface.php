<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Entity\UserTokenEntity;

interface UserTokenRepositoryInterface
{
    public function save(UserTokenEntity $userToken): UserTokenEntity;
}
