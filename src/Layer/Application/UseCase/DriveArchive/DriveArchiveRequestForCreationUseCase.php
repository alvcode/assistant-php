<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveArchive;

use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Application\Exception\DriveArchive\DriveArchiveExistsActiveJobsException;
use App\Layer\Application\Exception\DriveArchive\DriveArchiveJobNotFoundFilesException;
use App\Layer\Domain\Dict\Drive\DriveArchiveJobStatusEnum;
use App\Layer\Domain\Dict\Drive\DriveStructTypeEnum;
use App\Layer\Domain\Entity\DriveArchiveJobEntity;
use App\Layer\Domain\Repository\DriveArchiveRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Repository\QueueRepositoryInterface;
use App\Layer\Domain\Service\Factory\Drive\DriveArchiveFactory;
use Generator;

final readonly class DriveArchiveRequestForCreationUseCase
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
     * @throws DriveArchiveExistsActiveJobsException
     * @throws DriveArchiveJobNotFoundFilesException
     */
    public function handle(int $userId, array $structIds): DriveArchiveJobEntity
    {
        $existsActiveJobs = $this->driveArchiveRepository->existsJobsByUserAndStatuses(
            userId: $userId,
            statuses: [
                DriveArchiveJobStatusEnum::New,
                DriveArchiveJobStatusEnum::Processed,
                DriveArchiveJobStatusEnum::Completed,
            ]
        );
        if ($existsActiveJobs) {
            throw new DriveArchiveExistsActiveJobsException('Уже есть активные задания на архивацию');
        }
        $checkedStructCount = $this->driveStructRepository->structCountByUserAndIds(
            userId: $userId,
            structIds: $structIds,
            includeRecycleBin: false,
        );
        if (count($structIds) !== $checkedStructCount) {
            throw new DriveStructNotFoundException('Структура(-ы) не найдена');
        }

        if (!$this->existsFiles($userId, $structIds)) {
            throw new DriveArchiveJobNotFoundFilesException('Для создания архива нужен хотя бы 1 файл');
        }

        $driveArchiveJobEntity = $this->driveArchiveRepository->save(
            $this->driveArchiveFactory->getNewDriveArchiveJob($userId, $structIds),
        );
        $this->queueRepository->pushDriveArchive($driveArchiveJobEntity->getId());

        return $driveArchiveJobEntity;
    }

    /** @param int[] $structIds */
    private function existsFiles(int $userId, array $structIds): bool
    {
        foreach ($structIds as $structId) {
            foreach ($this->getRecursiveTree($userId, $structId) as $driveTreeDto) {
                if ($driveTreeDto->type === DriveStructTypeEnum::File) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getRecursiveTree(int $userId, int $structId): Generator
    {
        $structEntity = $this->driveStructRepository->getById($structId, false);
        if ($structEntity->getType() === DriveStructTypeEnum::File) {
            yield $structEntity;
        } else {
            $tree = $this->driveStructRepository->getTreeByUserID($userId, $structEntity->getId());
            foreach ($tree as $driveTreeDto) {
                if ($driveTreeDto->type === DriveStructTypeEnum::File) {
                    yield $driveTreeDto;
                } else {
                    yield from $this->getRecursiveTree($userId, $driveTreeDto->id);
                }
            }
        }
    }
}
