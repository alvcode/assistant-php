<?php

declare(strict_types=1);

namespace App\Layer\Application\Exception\Drive;

use App\Layer\Domain\Exception\AbstractLogicException;

class DriveStructIsNotChunkException extends AbstractLogicException
{
    public function getErrorKey(): string
    {
        return 'error_struct_is_not_chunk';
    }
}
