<?php

declare(strict_types=1);

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class NoteBlocks extends Constraint
{
    public string $errorKey = 'error_invalid_field_format';
}
