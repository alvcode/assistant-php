<?php

namespace App\Infrastructure;

use Symfony\Contracts\Translation\TranslatorInterface;

class Lang
{
    private static TranslatorInterface $translator;

    public static function setTranslator(TranslatorInterface $translator): void
    {
        self::$translator = $translator;
    }

    public static function t(string $key): string
    {
        return self::$translator->trans($key);
    }
}
