<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Repository\DTO\Storage\SaveFileDTO;
use App\Layer\Domain\ValueObject\SplFileInfoVO;

interface StorageRepositoryInterface
{
    public function save(SaveFileDTO $in): void;

    public function getFile(string $path): SplFileInfoVO;

    public function delete(string $path): void;

    /** @param string[] $paths */
    public function deleteAll(array $paths): void;

    public function isExists(string $path): bool;
}
