<?php

declare(strict_types=1);

namespace App\Layer\Application\Exception\NoteCategory;

use App\Layer\Domain\Exception\AbstractLogicException;

class NoteCategoryNotFoundException extends AbstractLogicException
{
    public function getErrorKey(): string
    {
        return 'error_category_not_found';
    }
}
