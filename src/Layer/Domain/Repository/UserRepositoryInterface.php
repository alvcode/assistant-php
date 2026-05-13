<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

interface UserRepositoryInterface
{
    public function existsByLogin(string $login): bool;
}
