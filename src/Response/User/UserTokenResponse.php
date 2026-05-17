<?php

declare(strict_types=1);

namespace App\Response\User;

use App\Layer\Domain\Entity\UserTokenEntity;

final class UserTokenResponse
{
    public function __construct(
        public int $user_id,
        public string $token,
        public string $refresh_token,
        public int $expired_to,
    ) {}

    public static function fromUserTokenEntity(UserTokenEntity $in): self
    {
        return new self(
            user_id: $in->getUserId(),
            token: $in->getToken(),
            refresh_token: $in->getRefreshToken(),
            expired_to: $in->getExpiredTo(),
        );
    }
}
