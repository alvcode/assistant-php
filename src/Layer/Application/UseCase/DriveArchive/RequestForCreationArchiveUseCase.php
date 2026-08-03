<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveArchive;

use App\Layer\Domain\Entity\DriveArchiveJobEntity;
use App\Layer\Domain\Repository\DriveArchiveRepositoryInterface;
use App\Layer\Domain\Service\Factory\Drive\DriveArchiveFactory;

final readonly class RequestForCreationArchiveUseCase
{
    public function __construct(
        private DriveArchiveRepositoryInterface $driveArchiveRepository,
        private DriveArchiveFactory $driveArchiveFactory,
    ) {}

    /** @param int[] $structIds */
    public function handle(int $userId, array $structIds): DriveArchiveJobEntity
    {
        // TODO: должны отправить в очередь
        return $this->driveArchiveRepository->save(
            $this->driveArchiveFactory->getNewDriveArchiveJob($userId, $structIds),
        );
    }
}
