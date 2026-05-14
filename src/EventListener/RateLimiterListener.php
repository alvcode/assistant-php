<?php

namespace App\EventListener;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RateLimiterListener
{
    private int $allowance;
    private int $durationSeconds;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->allowance = (int)$parameterBag->get('rateLimiter.allowance');
        $this->durationSeconds = (int)$parameterBag->get('rateLimiter.durationSeconds');
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $ip = $event->getRequest()->getClientIp();

        var_dump($ip);
        exit();
    }
}
