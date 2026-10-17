<?php

declare(strict_types=1);

namespace App\Infrastructure;

final readonly class FormatDict
{
    public const string DATETIME_ISO_8601_UTC = 'Y-m-d\TH:i:s\Z';
    public const string DB_DATETIME = 'Y-m-d H:i:s';
}
