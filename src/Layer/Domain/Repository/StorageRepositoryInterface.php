<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Domain\Repository\DTO\Storage\SaveFileDTO;
use App\Layer\Domain\ValueObject\PathVO;
use App\Layer\Domain\ValueObject\SplFileInfoVO;

interface StorageRepositoryInterface
{
    public function save(SaveFileDTO $in): void;

    public function getFile(PathVO $path): SplFileInfoVO;

    public function delete(PathVO $path): void;

    /** @param PathVO[] $paths */
    public function deleteAll(array $paths): void;

    public function isExists(PathVO $path): bool;
}
