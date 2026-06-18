<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Drive;

use App\Layer\Application\Exception\Drive\DriveNotSafeFilenameException;
use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Service\Utils\DateTime;

final readonly class DriveRenameStructUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
    ) {}

    /** @throws DriveStructNotFoundException */
    public function handle(int $structId, string $newName, int $userId): void
    {
        $driveStructEntity = $this->driveStructRepository->getById($structId);
        if (!$driveStructEntity || $driveStructEntity->getUserId() !== $userId) {
            throw new DriveStructNotFoundException('Структура не найдена');
        }

        if (
            str_contains($newName, '..')
            || str_contains($newName, '/')
            || str_contains($newName, '\\')
        ) {
            throw new DriveNotSafeFilenameException('Небезопасное имя файла');
        }

        $driveStructEntity->setName($newName);
        $driveStructEntity->setUpdatedAt(DateTime::createNowUtc());
        $this->driveStructRepository->save($driveStructEntity);
    }
}