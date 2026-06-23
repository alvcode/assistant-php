<?php

declare(strict_types=1);

namespace App\Request\Drive;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints;

class DriveDeleteStructRequest extends BaseRequest
{
    #[Constraints\NotBlank]
    #[Constraints\Type('digit')]
    #[Constraints\Range(min: 0, max: 1)]
    public mixed $force = null;

    public function getForceVal(): bool
    {
        return $this->force === '1';
    }
}
