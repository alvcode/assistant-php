<?php

declare(strict_types=1);

namespace App\Request\NoteCategories;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class CreateNoteCategoryRequest extends BaseRequest
{
    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    #[Assert\Length(max: 255)]
    public mixed $name;

    #[Assert\Type('integer')]
    #[Assert\Range(max: self::INT_4_MAX)]
    public mixed $parent_id = null;
}
