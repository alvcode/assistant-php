<?php

declare(strict_types=1);

namespace App\Request\Drive;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints;

final class DriveChunkPrepareRequest extends BaseRequest
{
    #[Constraints\Type('string')]
    #[Constraints\NotBlank()]
    #[Constraints\Length(min: 1, max: 300)]
    public mixed $filename;

    #[Constraints\Type('integer')]
    #[Constraints\NotBlank()]
    public mixed $full_size;

    #[Constraints\Type('integer')]
    public mixed $parent_id = null;

    #[Constraints\Type('string')]
    public mixed $sha256 = null;
}