<?php

declare(strict_types=1);

namespace App\Layer\Application\Exception\DriveRecycleBin;

use App\Layer\Domain\Exception\AbstractLogicException;

class DriveRecycleBinNotFoundException extends AbstractLogicException
{
    public function getErrorKey(): string
    {
        return 'error_drive_recycle_bin_not_found';
    }
}
