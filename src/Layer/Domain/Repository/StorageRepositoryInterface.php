<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Repository\DTO\Storage\SaveFileDTO;
use SplFileInfo;

interface StorageRepositoryInterface
{
    public function save(SaveFileDTO $in): void;

    public function getFile(string $path): SplFileInfo;

    public function delete(string $path): void;

    /** @param string[] $paths */
    public function deleteAll(array $paths): void;
}
