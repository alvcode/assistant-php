<?php

declare(strict_types=1);

namespace App\Layer\Application\Exception\NoteFile;

use App\Layer\Domain\Exception\AbstractLogicException;

class NoteFilesystemIsFullException extends AbstractLogicException
{
    public function getErrorKey(): string
    {
        return 'error_file_system_is_full';
    }
}
