<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveArchive;

use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Domain\Entity\DriveArchiveJobEntity;
use App\Layer\Domain\Repository\DriveArchiveRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Repository\QueueRepositoryInterface;
use App\Layer\Domain\Service\Factory\Drive\DriveArchiveFactory;

final readonly class RequestForCreationArchiveUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
        private DriveArchiveRepositoryInterface $driveArchiveRepository,
        private DriveArchiveFactory $driveArchiveFactory,
        private QueueRepositoryInterface $queueRepository,
    ) {}

    /**
     * @param int[] $structIds
     * @throws DriveStructNotFoundException
     */
    public function handle(int $userId, array $structIds): DriveArchiveJobEntity
    {
        $checkedStructCount = $this->driveStructRepository->structCountByUserAndIds(
            userId: $userId,
            structIds: $structIds,
            includeRecycleBin: false,
        );
        if (count($structIds) !== $checkedStructCount) {
            throw new DriveStructNotFoundException('Структура(-ы) не найдена');
        }

        $driveArchiveJobEntity = $this->driveArchiveRepository->save(
            $this->driveArchiveFactory->getNewDriveArchiveJob($userId, $structIds),
        );
        $this->queueRepository->pushDriveArchive($driveArchiveJobEntity->getId());

        return $driveArchiveJobEntity;
    }
}
