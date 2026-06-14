<?php

declare(strict_types=1);

namespace App\Layer\Application\Exception\Drive;

use App\Layer\Domain\Exception\AbstractLogicException;

class DriveFileNotFoundException extends AbstractLogicException
{
    public function getErrorKey(): string
    {
        return 'error_file_not_found';
    }
}
