<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class LocaleListener
{
    private array $supportedLocales = ['en', 'ru'];
    private string $defaultLocale;

    public function __construct(string $defaultLocale = 'ru')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $locale = $request->headers->get('locale', $this->defaultLocale);

        if (!in_array($locale, $this->supportedLocales, true)) {
            $locale = $this->defaultLocale;
        }
        $request->setLocale($locale);
    }
}
