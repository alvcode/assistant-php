<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Context\RateLimiterContext;
use App\Infrastructure\Lang;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

final class RateLimiterListener
{
    private int $allowance;
    private int $durationSeconds;
    private EntityManagerInterface $entityManager;
    private RateLimiterContext $context;

    public function __construct(
        ParameterBagInterface $parameterBag,
        EntityManagerInterface $entityManager,
        RateLimiterContext $context,
    )
    {
        $this->allowance = (int)$parameterBag->get('rateLimiter.allowance');
        $this->durationSeconds = (int)$parameterBag->get('rateLimiter.durationSeconds');
        $this->entityManager = $entityManager;
        $this->context = $context;
    }

    /**
     * @throws Exception
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $ip = $event->getRequest()->getClientIp();
        if (!$ip) {
            throw new Exception(Lang::t('error_unable_to_determine_ip_address'));
        }

        $currentAllowance = $this->getOne($ip, time(), $this->durationSeconds);
        if (!is_null($currentAllowance) && $currentAllowance <= 0) {
            throw new TooManyRequestsHttpException(null, Lang::t('error_too_many_requests'));
        }

        $allowance = $this->upsert($ip, $this->allowance, time(), $this->durationSeconds);

        $this->context->setLimit($this->allowance);
        $this->context->setRemaining($allowance);

        if ($allowance <= 0) {
            throw new TooManyRequestsHttpException(null, Lang::t('error_too_many_requests'));
        }
    }

    private function getOne(string $ip, int $nowTimestamp, int $durationSeconds): ?int
    {
        $query = "
            select allowance from rate_limiter where ip = :ip and timestamp > :compareTime
        ";

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query, [
            'ip' => $ip,
            'compareTime' => $nowTimestamp - $durationSeconds,
        ]);
        $result = $stmt->fetchOne();
        return $result !== false ? $result : null;
    }

    private function upsert(string $ip, int $allowance, int $nowTimestamp, int $durationSeconds): int
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
