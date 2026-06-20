<?php

declare(strict_types=1);

namespace App\Request\Auth;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints;

final class ChangePasswordRequest extends BaseRequest
{
    #[Constraints\Type('string')]
    #[Constraints\NotBlank]
    #[Constraints\Length(max: 200)]
    public mixed $current_password;

    #[Constraints\Type('string')]
    #[Constraints\NotBlank]
    #[Constraints\Length(max: 200)]
    public mixed $new_password;
}
