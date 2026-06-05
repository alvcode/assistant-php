<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

interface ConfigRepositoryInterface
{
    /** @return int in bytes */
    public function getNoteFileStorageLimitPerUser(): int;
}
