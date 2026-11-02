<?php

declare(strict_types=1);

namespace App\Layer\Domain\Dict\Drive;

enum DriveArchiveJobPlaceEnum: int
{
    case Common = 0;
    case DriveShare = 1;
}
