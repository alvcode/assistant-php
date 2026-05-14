<?php

declare(strict_types=1);

namespace App\Layer\Application\DTO\User;

use DateTime;
use DateTimeImmutable;

final readonly class UserDTO
{
    public function __construct(
        public int $id,
        public string $login,
        public DateTimeImmutable $createdAt,
        public DateTime $updatedAt,
    ) {}
}
