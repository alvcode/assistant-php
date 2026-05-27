<?php

declare(strict_types=1);

namespace App\Request\Notes;

use App\Request\BaseRequest;
use App\Validator\NoteBlocks;
use Symfony\Component\Validator\Constraints as Assert;

class CreateNoteRequest extends BaseRequest
{
    #[Assert\Type('integer')]
    #[Assert\NotBlank()]
    public mixed $category_id;

    #[Assert\Type('string')]
    #[Assert\Length(max: 150)]
    public mixed $title = null;

    #[NoteBlocks]
    public array $note_blocks;
}
