<?php

declare(strict_types=1);

namespace App\Layer\Application\Exception\NoteCategory;

use App\Layer\Domain\Exception\AbstractLogicException;

class CategoryHasNotesException extends AbstractLogicException
{
    public function getErrorKey(): string
    {
        return 'error_category_has_notes';
    }
}
