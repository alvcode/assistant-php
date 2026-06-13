<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Utils;

use DateMalformedStringException;
use DateTime as BaseDateTime;
use DateTimeZone;

class DateTime extends BaseDateTime
{
    /**
     * @throws DateMalformedStringException
     */
    public static function createNowUtc(): self
    {
        return new self('now', new DateTimeZone('UTC'));
    }

    /**
     * @throws DateMalformedStringException
     */
    public static function createUTCFromString(string $dateTime): self
    {
        return new self($dateTime, new DateTimeZone('UTC'));
    }
}
