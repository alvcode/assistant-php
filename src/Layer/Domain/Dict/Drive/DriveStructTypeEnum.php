<?php

declare(strict_types=1);

namespace App\Layer\Domain\Dict\Drive;

enum DriveStructTypeEnum: int
{
    case Directory = 0;
    case File = 1;
}
