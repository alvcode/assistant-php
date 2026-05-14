<?php

declare(strict_types=1);

namespace App\Layer\Application\Exception\User;

use App\Layer\Domain\Exception\AbstractLogicException;

class UserAlreadyExistsException extends AbstractLogicException
{
    public function getErrorKey(): string
    {
        return 'error_user_already_exists';
    }
}
