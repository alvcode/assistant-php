<?php

declare(strict_types=1);

namespace App\Layer\Application\Exception\Drive;

use App\Layer\Domain\Exception\AbstractLogicException;

class DriveRelocatableStructureNotFoundException extends AbstractLogicException
{
    public function getErrorKey(): string
    {
        return 'error_drive_relocatable_structure_not_found';
    }
}
