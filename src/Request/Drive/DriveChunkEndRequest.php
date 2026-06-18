<?php

declare(strict_types=1);

namespace App\Request\Drive;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints;

final class DriveChunkEndRequest extends BaseRequest
{
    #[Constraints\Type('integer')]
    #[Constraints\NotBlank()]
    #[Constraints\Range(max: self::INT_4_MAX)]
    public mixed $struct_id;
}