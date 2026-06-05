<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\ValueObject\FileSizeVO;

interface NoteFileRepositoryInterface
{
    public function getUsedSpaceByUserID(int $userID): FileSizeVO;
}
