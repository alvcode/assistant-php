<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Repository\DTO\Storage\SaveFileDTO;
use App\Layer\Domain\ValueObject\FileContentVO;

interface StorageRepositoryInterface
{
    public function save(SaveFileDTO $in): void;

    public function getFile(string $path): FileContentVO;
}
