<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

use App\Layer\Infrastructure\DTO\Drive\DriveTreeDTO;

interface DriveStructRepositoryInterface
{
    /** @return DriveTreeDTO[] */
    public function getTreeByUserID(int $userID, ?int $parentID): array;
}
