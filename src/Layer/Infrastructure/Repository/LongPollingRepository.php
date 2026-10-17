<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Infrastructure\FormatDict;
use App\Layer\Domain\Repository\ClientNotificationRepositoryInterface;
use App\Layer\Domain\Service\Utils\DateTimeImmutable;
use App\Layer\Domain\ValueObject\ClientNotification\ClientNotificationInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class LongPollingRepository implements ClientNotificationRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function pushEvent(ClientNotificationInterface $event): void
    {
        $params = [
            'user_id' => $event->getUserId(),
            'event_key' => $event->getEventKey(),
            'payload' => json_encode($event->getPayload(), JSON_THROW_ON_ERROR),
            'expired_at' => DateTimeImmutable::createNowUtc()
                ->modify('+' . $event->getLifetimeSeconds() . ' seconds')
                ->format(FormatDict::DB_DATETIME),
        ];

        $query = "
            insert into long_polling_events (user_id, event_key, payload, expired_at)
            values (:user_id, :event_key, :payload, :expired_at)
        ";

        $conn = $this->entityManager->getConnection();
        $conn->executeQuery($query, $params);
    }
}
