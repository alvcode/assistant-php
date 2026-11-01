<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Drive;

use App\Layer\Application\Exception\Drive\DriveFileNotFoundException;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Repository\DTO\Drive\DriveTreeDTO;

final readonly class DriveGetTreeItemUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
    ) {}

    /**
     * @throws DriveFileNotFoundException
     */
    public function handle(int $structId, int $userId): DriveTreeDTO
    {
        $driveStructEntity = $this->driveStructRepository->getById($structId, false);
        if (
            is_null($driveStructEntity)
            || $driveStructEntity->getUserId() !== $userId
        ) {
            throw new DriveFileNotFoundException('Структура не найдена');
        }

        return $this->driveStructRepository->getTreeItemById($structId, $userId);
    }
}
