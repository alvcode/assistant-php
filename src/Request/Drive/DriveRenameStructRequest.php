<?php

declare(strict_types=1);

namespace App\Request\Drive;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints;

final class DriveRenameStructRequest extends BaseRequest
{
    #[Constraints\Type('string')]
    #[Constraints\NotBlank()]
    #[Constraints\Length(min: 1, max: 350)]
    public mixed $name;
}