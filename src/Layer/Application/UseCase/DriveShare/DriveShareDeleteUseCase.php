<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveShare;

use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Domain\Repository\DriveShareHashesRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;

final readonly class DriveShareDeleteUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
        private DriveShareHashesRepositoryInterface $shareHashesRepository,
    ) {}

    /**
     * @throws DriveStructNotFoundException
     */
    public function handle(int $structID, int $userID): void
    {
        $noteBelongsUser = $this->driveStructRepository->isBelongToUser($structID, $userID);
        if (!$noteBelongsUser) {
            throw new DriveStructNotFoundException('Заметка не найдена');
        }

        $this->shareHashesRepository->deleteByStructId($structID);
    }
}
