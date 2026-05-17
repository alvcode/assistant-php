<?php

declare(strict_types=1);

namespace App\Layer\Application\Exception\User;

use App\Layer\Domain\Exception\AbstractLogicException;

class InvalidCredentialsException extends AbstractLogicException
{
    public function getErrorKey(): string
    {
        return 'error_invalid_credentials';
    }
}
