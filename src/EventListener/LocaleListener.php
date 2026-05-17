<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

final class LocaleListener
{
    private array $supportedLocales = ['en', 'ru'];
    private string $defaultLocale;

    public function __construct(
        string $defaultLocale = 'ru',
        private TranslatorInterface $translator,
    )
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
        $this->translator->setLocale($locale);
    }
}
