<?php

declare(strict_types=1);

namespace App\Security\BlockEvent;

use App\Infrastructure\FormatDict;
use App\Infrastructure\Lang;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class BlockEventService
{
    private const checkMinutes = 40;

    public function __construct(
        private ParameterBagInterface $parameterBag,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @throws Exception
     */
    public function setEvent(Request $request, BlockEventTypeEnum $type): void
    {
        $paranoia = BlockEventParanoiaEnum::tryFrom((int)$this->parameterBag->get('app.blockingParanoia'));
        if (is_null($paranoia)) {
            throw new Exception('Не удалось определить настройку BlockEventParanoia');
        }
        if ($paranoia === BlockEventParanoiaEnum::Off) {
            return;
        }

        $ip = $request->getClientIp();
        if (!$ip) {
            throw new Exception(Lang::t('error_unable_to_determine_ip_address'));
        }



        $this->saveEvent($ip, $type->value, new DateTimeImmutable('now', new DateTimeZone('UTC')));

        $stats = $this->getStats(
            $ip,
            (new DateTimeImmutable('now', new DateTimeZone('UTC')))
                ->modify('-' . self::checkMinutes . ' minutes')
        );

        foreach (BlockEventTypeEnum::cases() as $blockEventType) {
            if (
                isset($stats[$blockEventType->value]) &&
                $stats[$blockEventType->value] >= $blockEventType->getMaxCount($paranoia)
            ) {
                $this->blockIP(
                    $ip,
                    (new DateTimeImmutable('now', new DateTimeZone('UTC')))
                        ->modify('+' . $paranoia->blockingMinutes() . ' minutes')
                );
            }
        }
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function blockIP(string $ip, DateTimeImmutable $unblockedAt): void
    {
        $query = "insert into block_ip (ip, blocked_until) values (:ip, :unblockedAt)";
        $conn = $this->entityManager->getConnection();
        $conn->executeQuery(
            $query,
            ['ip' => $ip, 'unblockedAt' => $unblockedAt->format(FormatDict::DB_DATETIME)]
        );
    }

    private function saveEvent(string $ip, string $event, DateTimeImmutable $now): void
    {
        $query = "
            insert into block_events(ip, event, created_at)
            values(:ip, :event, :now)
        ";

        $conn = $this->entityManager->getConnection();
        $conn->executeQuery($query, [
            'ip' => $ip,
            'event' => $event,
            'now' => $now->format(FormatDict::DB_DATETIME),
        ]);
    }

    /**
     * @return array<string,int> <eventType,count>
     * @throws \Doctrine\DBAL\Exception
     */
    private function getStats(string $ip, DateTimeImmutable $checkTime): array
    {
        $query = "
            WITH error_counts AS (
                select event, COUNT(*) as event_count
                from block_events
                where ip = :ip
                and created_at >= :checkTime
                GROUP BY event
            )
            SELECT ec.event, ec.event_count
            FROM error_counts ec
        ";

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, [
            'ip' => $ip,
            'checkTime' => $checkTime->format(FormatDict::DB_DATETIME),
        ]);

        return array_column($stmt->fetchAllAssociative(), 'event_count', 'event');
    }
}

