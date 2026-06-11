<?php

declare(strict_types=1);

namespace App\Request\Notes;

use App\Request\BaseRequest;
use App\Validator\NoteBlocks;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateNoteRequest extends BaseRequest
{
    #[Assert\Type('integer')]
    #[Assert\NotBlank()]
    #[Assert\Range(max: self::INT_4_MAX)]
    public mixed $id;

    #[Assert\Type('integer')]
    #[Assert\NotBlank()]
    #[Assert\Range(max: self::INT_4_MAX)]
    public mixed $category_id;

    #[Assert\Type('string')]
    #[Assert\Length(max: 150)]
    public mixed $title = null;

    #[NoteBlocks]
    public array $note_blocks;
}
