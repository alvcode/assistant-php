<?php

declare(strict_types=1);

namespace App\Request\Common;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class IDRequest extends BaseRequest
{
    #[Assert\Type('integer')]
    #[Assert\NotBlank()]
    public mixed $id;
}
