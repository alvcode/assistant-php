<?php

declare(strict_types=1);

namespace App\Layer\Application\Exception\User;

use App\Layer\Domain\Exception\AbstractLogicException;

class RefreshTokenNotFoundException extends AbstractLogicException
{
    public function getErrorKey(): string
    {
        return 'error_refresh_token_not_found';
    }
}
