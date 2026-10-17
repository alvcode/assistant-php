<?php

declare(strict_types=1);

namespace App\Layer\Domain\Dict\Drive;

enum DriveArchiveJobStatusEnum: int
{
    case New = 0;
    case Processed = 1;
    case Completed = 2;
    case Failed = 3;
    case Deleted = 4;
}
