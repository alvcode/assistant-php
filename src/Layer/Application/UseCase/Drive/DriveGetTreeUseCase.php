<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Drive;

use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Repository\DTO\Drive\DriveTreeDTO;

final readonly class DriveGetTreeUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
    ) {}

    /** @return DriveTreeDTO[] */
    public function handle(int $userID, ?int $parentID): array
    {
        return $this->driveStructRepository->getTreeByUserID($userID, $parentID);
    }
}
