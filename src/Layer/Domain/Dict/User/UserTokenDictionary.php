<?php

declare(strict_types=1);

namespace App\Layer\Domain\Dict\User;

final readonly class UserTokenDictionary
{
    public const TOKEN_LENGTH = 87;
    public const REFRESH_TOKEN_LENGTH = 54;
    public const TOKEN_LIFE_TIME_HOURS = 4;
}
