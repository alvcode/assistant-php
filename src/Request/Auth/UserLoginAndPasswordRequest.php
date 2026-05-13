<?php

declare(strict_types=1);

namespace App\Request\Auth;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

final class UserLoginAndPasswordRequest extends BaseRequest
{
    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    #[Assert\Length(max: 100)]
    public $login;

    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    #[Assert\Length(max: 200)]
    public $password;
}
