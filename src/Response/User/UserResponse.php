<?php

declare(strict_types=1);

namespace App\Response\User;

use App\Infrastructure\FormatDict;
use App\Layer\Application\DTO\User\UserDTO;

final class UserResponse
{
    public function __construct(
        public int $id,
        public string $login,
        public string $created_at,
        public string $updated_at,
    ) {}

    public static function fromUserDTO(UserDTO $userDTO): self
    {
        return new self(
            id: $userDTO->id,
            login: $userDTO->login,
            created_at: $userDTO->createdAt->format(FormatDict::DATETIME_ISO_8601_UTC),
            updated_at: $userDTO->updatedAt->format(FormatDict::DATETIME_ISO_8601_UTC),
        );
    }
}
