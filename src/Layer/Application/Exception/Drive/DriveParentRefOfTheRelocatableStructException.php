<?php

declare(strict_types=1);

namespace App\Layer\Application\Exception\Drive;

use App\Layer\Domain\Exception\AbstractLogicException;

class DriveParentRefOfTheRelocatableStructException extends AbstractLogicException
{
    public function getErrorKey(): string
    {
        return 'error_drive_parent_references_one_of_the_relocatable_struct';
    }
}
