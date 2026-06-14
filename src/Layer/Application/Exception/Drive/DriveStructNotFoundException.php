<?php

declare(strict_types=1);

namespace App\Layer\Application\Exception\Drive;

use App\Layer\Domain\Exception\AbstractLogicException;

class DriveStructNotFoundException extends AbstractLogicException
{
    public function getErrorKey(): string
    {
        return 'error_drive_struct_not_found';
    }
}
