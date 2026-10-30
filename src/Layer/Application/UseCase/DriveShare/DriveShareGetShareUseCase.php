<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveShare;

use App\Layer\Application\Exception\Common\ShareLinkNotFoundException;
use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Domain\Entity\DriveShareEntity;
use App\Layer\Domain\Repository\DriveShareHashesRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;

final readonly class DriveShareGetShareUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
        private DriveShareHashesRepositoryInterface $shareHashesRepository,
    ) {}

    /**
     * @throws DriveStructNotFoundException
     * @throws ShareLinkNotFoundException
     */
    public function handle(int $structID, int $userID): DriveShareEntity
    {
        $noteBelongsUser = $this->driveStructRepository->isBelongToUser($structID, $userID);
        if (!$noteBelongsUser) {
            throw new DriveStructNotFoundException('Заметка не найдена');
        }

        $shareEntity = $this->shareHashesRepository->getByStructId($structID);
        if (!$shareEntity) {
            throw new ShareLinkNotFoundException('share-ссылка не найдена');
        }
        return $shareEntity;
    }
}
