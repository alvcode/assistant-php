<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Context\RateLimiterContext;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

final readonly class RateLimiterResponseListener
{
    public function __construct(
        private RateLimiterContext $context,
    ) {}

    public function onKernelResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();

        if ($this->context->limit !== null) {
            $response->headers->set('X-Rate-Limit-Limit', (string)$this->context->getLimit());
        }

        if ($this->context->remaining !== null) {
            $response->headers->set('X-Rate-Limit-Remaining', (string)$this->context->getRemaining());
        }
    }
}
