<?php

namespace App\EventListener;

use App\Infrastructure\Lang;
use App\Layer\Domain\Repository\RateLimiterRepositoryInterface;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class RateLimiterListener
{
    private int $allowance;
    private int $durationSeconds;
    private RateLimiterRepositoryInterface $rateLimiterRepository;

    public function __construct(
        ParameterBagInterface $parameterBag,
        RateLimiterRepositoryInterface $rateLimiterRepository,
    )
    {
        $this->allowance = (int)$parameterBag->get('rateLimiter.allowance');
        $this->durationSeconds = (int)$parameterBag->get('rateLimiter.durationSeconds');
        $this->rateLimiterRepository = $rateLimiterRepository;
    }

    /**
     * @throws Exception
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $ip = $event->getRequest()->getClientIp();
        if (!$ip) {
            throw new Exception('Unable to determine IP address');
        }

        $allowance = $this->rateLimiterRepository->upsert($ip, $this->allowance, time(), $this->durationSeconds);
        var_dump(Lang::t('error_file_not_found'));
        exit();
        if ($allowance <= 0) {
            throw new TooManyRequestsHttpException(null, Lang::t('error_too_many_requests'));
        }
    }
}
