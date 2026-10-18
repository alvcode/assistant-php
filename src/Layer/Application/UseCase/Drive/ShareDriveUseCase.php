<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Drive;

use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;

final readonly class ShareDriveUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
    )
    {
    }

    /**
     * @throws DriveStructNotFoundException
     */
    public function create(int $structID, int $userID)
    {
        $this->assertBelongToUser($structID, $userID);


    }

    /**
     * @throws DriveStructNotFoundException
     */
    private function assertBelongToUser(int $structID, int $userID): void
    {
        $noteBelongsUser = $this->driveStructRepository->isBelongToUser($structID, $userID);
        if (!$noteBelongsUser) {
            throw new DriveStructNotFoundException('Заметка не найдена');
        }
    }
}
