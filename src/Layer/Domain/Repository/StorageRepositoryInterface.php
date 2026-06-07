<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Repository\DTO\Storage\SaveFileDTO;

interface StorageRepositoryInterface
{
    public function save(SaveFileDTO $in): void;
}
