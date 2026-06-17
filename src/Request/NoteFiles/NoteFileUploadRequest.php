<?php

declare(strict_types=1);

namespace App\Request\NoteFiles;

use App\Request\BaseRequest;
use App\Validator\SafeFileName;
use App\Validator\UploadedNoteFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class NoteFileUploadRequest extends BaseRequest
{
    #[Assert\NotBlank]
    #[SafeFileName]
    #[UploadedNoteFile]
    public ?UploadedFile $file = null;
}
