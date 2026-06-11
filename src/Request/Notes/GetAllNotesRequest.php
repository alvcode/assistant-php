<?php

declare(strict_types=1);

namespace App\Request\Notes;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class GetAllNotesRequest extends BaseRequest
{
    #[Assert\Type('digit')]
    #[Assert\NotBlank()]
    #[Assert\Range(max: self::INT_4_MAX)]
    public mixed $categoryId;
}
