<?php

declare(strict_types=1);

namespace App\Request\Auth;

use App\Layer\Domain\Dict\User\UserTokenDictionary;
use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class RefreshTokenRequest extends BaseRequest
{
    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    #[Assert\Length(min: UserTokenDictionary::TOKEN_LENGTH, max: UserTokenDictionary::TOKEN_LENGTH)]
    #[Assert\Regex(pattern: '/^[^\s]+$/')]
    public mixed $token;

    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    #[Assert\Length(min: UserTokenDictionary::REFRESH_TOKEN_LENGTH, max: UserTokenDictionary::REFRESH_TOKEN_LENGTH)]
    #[Assert\Regex(pattern: '/^[^\s]+$/')]
    public mixed $refresh_token;
}
