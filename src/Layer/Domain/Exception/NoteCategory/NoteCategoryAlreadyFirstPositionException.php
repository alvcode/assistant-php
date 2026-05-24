<?php

declare(strict_types=1);

namespace App\Layer\Domain\Exception\NoteCategory;

use App\Layer\Domain\Exception\AbstractLogicException;

class NoteCategoryAlreadyFirstPositionException extends AbstractLogicException
{
    public function getErrorKey(): string
    {
        return 'error_category_already_in_1_position';
    }
}
