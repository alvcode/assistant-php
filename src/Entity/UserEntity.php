<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

class UserEntity implements UserInterface
{
    public function __construct(
        public int $id,
        public string $login,
    ) {}


    public function getRoles(): array
    {
        return ['user'];
    }

    public function getUserIdentifier(): string
    {
        return $this->login;
    }
}
