<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Infrastructure\FormatDict;
use App\Infrastructure\Lang;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final readonly class BlockIPListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @throws Exception
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $ip = $event->getRequest()->getClientIp();
        if (!$ip) {
            throw new Exception('Unable to determine IP address');
        }

        if ($this->findBlocking($ip, new DateTimeImmutable('now', new DateTimeZone('UTC')))) {
            throw new AccessDeniedHttpException('Access denied');
        }
    }

    private function findBlocking(string $ip, DateTimeImmutable $now): bool
    {
        $query = "
            SELECT EXISTS(SELECT 1 FROM block_ip WHERE ip = :ip and blocked_until > :dt)
        ";

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, [
            'ip' => $ip,
            'dt' => $now->format(FormatDict::DB_DATETIME),
        ]);

        return $stmt->fetchOne();
    }
}
