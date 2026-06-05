<?php

declare(strict_types=1);

namespace App\Layer\Domain\Dict\Common;

enum FileSizeTypeEnum
{
    case Bytes;
    case Kilobytes;
    case Megabytes;
    case Gigabytes;
}
