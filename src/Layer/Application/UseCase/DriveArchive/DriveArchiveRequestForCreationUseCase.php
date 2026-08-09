<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveArchive;

use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Application\Exception\DriveArchive\DriveArchiveExistsActiveJobsException;
use App\Layer\Application\Exception\DriveArchive\DriveArchiveJobNotFoundFilesException;
use App\Layer\Domain\Dict\Drive\DriveArchiveJobStatusEnum;
use App\Layer\Domain\Entity\DriveArchiveJobEntity;
use App\Layer\Domain\Repository\DriveArchiveRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Repository\QueueRepositoryInterface;
use App\Layer\Domain\Service\Drive\GetRecursiveFileStructsWithRealPath;
use App\Layer\Domain\Service\Factory\Drive\DriveArchiveFactory;

final readonly class DriveArchiveRequestForCreationUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
        private DriveArchiveRepositoryInterface $driveArchiveRepository,
        private DriveArchiveFactory $driveArchiveFactory,
        private QueueRepositoryInterface $queueRepository,
        private GetRecursiveFileStructsWithRealPath $getRecursiveFileStructsWithRealPath,
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
        $existsFile = false;
        foreach ($structIds as $structId) {
            foreach (
                $this->getRecursiveFileStructsWithRealPath->service(
                    $userId,
                    $structId,
                    ''
                ) as $driveStructWithRealPath
            ) {
                $existsFile = true;
                break;
            }

            if ($existsFile) {
                break;
            }
        }

        return $existsFile;
    }
}
