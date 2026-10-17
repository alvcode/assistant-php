<?php

declare(strict_types=1);

namespace App\Layer\Domain\ValueObject\ClientNotification;

interface ClientNotificationInterface
{
    public function getUserId(): int;

    public function getEventKey(): string;

    public function getPayload(): ?array;

    public function getLifetimeSeconds(): int;
}
