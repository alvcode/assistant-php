<?php

declare(strict_types=1);

namespace App\Layer\Domain\Entity\Aggregate;

final class DriveRBOutdatedDeleteAggregate
{
    public function __construct(
        private int $id,
        private int $userId,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }
}
