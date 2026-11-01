<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Drive;

use App\Layer\Application\Exception\Drive\DriveFileNotFoundException;
use App\Layer\Domain\Repository\DriveFileRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\ValueObject\FileSizeVO;

final readonly class DriveGetStructFullSizeUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
        private DriveFileRepositoryInterface $driveFileRepository,
    ) {}

    /**
     * @throws DriveFileNotFoundException
     */
    public function handle(int $structId, int $userId): FileSizeVO
    {
        $driveStructEntity = $this->driveStructRepository->getById($structId, false);
        if (
            is_null($driveStructEntity)
            || $driveStructEntity->getUserId() !== $userId
        ) {
            throw new DriveFileNotFoundException('Структура не найдена');
        }

        return $this->driveFileRepository->getTotalSizeByStructId($structId);
    }
}
