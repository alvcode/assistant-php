<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Repository\RateLimiterRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class RateLimiterRepository implements RateLimiterRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * @inheritDoc
     */
    public function upsert(string $ip, int $allowance, int $nowTimestamp, int $durationSeconds): int
    {
        $query = "
            INSERT INTO rate_limiter(ip, allowance, timestamp)
            VALUES (:ip, :allowance, :now)

            ON CONFLICT (ip)
            DO UPDATE
            SET
                allowance = CASE
                    WHEN rate_limiter.timestamp + :duration <= :now
                        THEN :allowance
                    ELSE rate_limiter.allowance - 1
                END,

                timestamp = CASE
                    WHEN rate_limiter.timestamp + :duration <= :now
                        THEN :now
                    ELSE rate_limiter.timestamp
                END

            RETURNING allowance
        ";

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, [
            'ip' => $ip,
            'allowance' => $allowance,
            'now' => $nowTimestamp,
            'duration' => $durationSeconds,
        ]);

        return $stmt->fetchOne();
    }
}
