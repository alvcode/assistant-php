<?php

declare(strict_types=1);

namespace App\Request\Drive;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints;

final class DriveRenMovStructsRequest extends BaseRequest
{
    #[Constraints\NotBlank()]
    #[Constraints\Type('array')]
    #[Constraints\All(
        constraints: [
            new Constraints\Type('integer'),
            new Constraints\Range(max: self::INT_4_MAX)
        ]
    )]
    public mixed $struct_ids = null;

    #[Constraints\Type('integer')]
    #[Constraints\Range(max: self::INT_4_MAX)]
    public mixed $parent_id = null;
}