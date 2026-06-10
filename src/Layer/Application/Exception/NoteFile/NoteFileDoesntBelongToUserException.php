<?php

declare(strict_types=1);

namespace App\Layer\Application\Exception\NoteFile;

use App\Layer\Domain\Exception\AbstractLogicException;

class NoteFileDoesntBelongToUserException extends AbstractLogicException
{
    public function getErrorKey(): string
    {
        return 'error_file_note_doesnt_belong_to_user';
    }
}
