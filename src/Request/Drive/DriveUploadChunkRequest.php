<?php

declare(strict_types=1);

namespace App\Request\Drive;

use App\Request\BaseRequest;
use App\Validator\SafeFileName;
use App\Validator\UploadedDriveFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints;

class DriveUploadChunkRequest extends BaseRequest
{
    #[Constraints\NotBlank()]
    #[SafeFileName]
    #[UploadedDriveFile]
    public ?UploadedFile $file = null;

    #[Constraints\Type('digit')]
    #[Constraints\NotBlank()]
    #[Constraints\Range(max: self::INT_4_MAX)]
    public mixed $structId = null;

    #[Constraints\Type('digit')]
    #[Constraints\NotBlank()]
    #[Constraints\Range(max: self::INT_2_MAX)]
    public mixed $chunkNumber = null;
}