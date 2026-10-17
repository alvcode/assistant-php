<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\ValueObject\ClientNotification\ClientNotificationInterface;

interface ClientNotificationRepositoryInterface
{
    public function pushEvent(ClientNotificationInterface $event): void;
}
