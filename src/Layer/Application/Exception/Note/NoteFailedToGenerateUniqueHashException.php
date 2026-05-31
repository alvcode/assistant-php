<?php

declare(strict_types=1);

namespace App\Layer\Application\Exception\Note;

use App\Layer\Domain\Exception\AbstractTechnicalException;

class NoteFailedToGenerateUniqueHashException extends AbstractTechnicalException
{
}
