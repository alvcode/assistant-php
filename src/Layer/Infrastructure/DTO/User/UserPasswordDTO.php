<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\DTO\User;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final class UserPasswordDTO implements PasswordAuthenticatedUserInterface
{
    public function __construct(
        private ?string $password,
    ) {}

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
