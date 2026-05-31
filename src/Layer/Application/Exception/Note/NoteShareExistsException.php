<?php

declare(strict_types=1);

namespace App\Layer\Application\Exception\Note;

use App\Layer\Domain\Exception\AbstractLogicException;

class NoteShareExistsException extends AbstractLogicException
{
    public function getErrorKey(): string
    {
        return 'error_note_share_exists';
    }
}
