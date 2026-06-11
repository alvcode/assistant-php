<?php

declare(strict_types=1);

namespace App\Request\Drive;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints;

class DriveWithParentIDRequest extends BaseRequest
{
    #[Constraints\Type('digit')]
    #[Constraints\Range(max: self::INT_4_MAX)]
    public mixed $parentId = null;
}
