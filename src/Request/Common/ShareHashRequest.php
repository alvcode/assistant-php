<?php

declare(strict_types=1);

namespace App\Request\Common;

use App\Layer\Domain\Dict\Common\ShareDict;
use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class ShareHashRequest extends BaseRequest
{
    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    #[Assert\Length(min: ShareDict::HASH_LENGTH, max: ShareDict::HASH_LENGTH)]
    #[Assert\Regex(pattern: self::REGEX_STRING_NO_WHITESPACE)]
    public mixed $hash;
}
