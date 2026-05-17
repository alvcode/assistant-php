<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Infrastructure\Lang;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class LangListener
{
    private TranslatorInterface $symfonyTranslator;

    public function __construct(TranslatorInterface $symfonyTranslator)
    {
        $this->symfonyTranslator = $symfonyTranslator;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        Lang::setTranslator($this->symfonyTranslator);
    }
}
