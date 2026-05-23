<?php

declare(strict_types=1);

namespace App\Layer\Application\Exception\NoteCategory;

use App\Layer\Domain\Exception\AbstractLogicException;

class ParentNoteCategoryNotFoundException extends AbstractLogicException
{
    public function getErrorKey(): string
    {
        return 'parent_id_of_the_category_not_found';
    }
}
