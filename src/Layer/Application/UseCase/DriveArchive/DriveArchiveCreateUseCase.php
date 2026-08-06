<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveArchive;

use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Application\Exception\DriveArchive\DriveArchiveJobNotFoundException;
use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\DriveArchiveRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Repository\QueueRepositoryInterface;
use App\Layer\Domain\Service\Factory\Drive\DriveArchiveFactory;
use App\Layer\Domain\Service\Factory\Storage\StorageRepositoryFactoryInterface;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;

final readonly class DriveArchiveCreateUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
        private DriveArchiveRepositoryInterface $driveArchiveRepository,
        private DriveArchiveFactory $driveArchiveFactory,
        private QueueRepositoryInterface $queueRepository,
        private ConfigRepositoryInterface $configRepository,
        private StorageRepositoryFactoryInterface $storageRepositoryFactory,
        private FileUtilsInterface $fileUtils,
    ) {}

    /**
     * @throws DriveArchiveJobNotFoundException
     */
    public function handle(int $driveArchiveJobId): void
    {
        $driveArchiveJobEntity = $this->driveArchiveRepository->getJobById($driveArchiveJobId);
        if (!$driveArchiveJobEntity) {
            throw new DriveArchiveJobNotFoundException('Archive Job не найден');
        }

        $processedStructIds = [];

        /**
         * создаем папку по ID джобы.
         * идем циклом по struct_ids
         * получаем структуру. если файл - получаем, кладем внутрь папки. сохраняем в $processedStructIds/
         * если папка, то создаем папку с этим именем, проваливаемся как бы в нее, получаем дерево, где она является parent_id,
         * и так рекурсивно обходим до конца.
         *
         * заходим на следующую итерацию.
         *
         * папка сформирована. делаем из нее архив. сохраняем path в jobEntity. удаляем первичную папку.
         * разбиваем архив на чанки и сохраняя эти чанки, где-то там рядом (подумать), сохраняя в drive_archive_file_chunks
         * пишем все в БД. удаляем архив.
         *
         * когда конец тогда конец ))))
         */
    }
}
