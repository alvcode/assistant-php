<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Drive;

use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileChunkRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileRepositoryInterface;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;

final readonly class DriveDeletedStructPathsService
{
    public function __construct(
        private ConfigRepositoryInterface $configRepository,
        private DriveFileChunkRepositoryInterface $driveFileChunkRepository,
        private FileUtilsInterface $fileUtils,
        private DriveFileRepositoryInterface $driveFileRepository,
    ) {}

    /** @return string[] */
    public function getPathsForDelete(int $structId, int $userId): array
    {
        $deletePaths = [];
        $baseSavePath = $this->configRepository->getDriveFileSavePath();
        $deleteChunkEntityList = $this->driveFileChunkRepository->getAllRecursive(
            $structId,
            $userId,
            false
        );

        foreach ($deleteChunkEntityList as $driveFileChunkEntity) {
            $deletePaths[] = $this->fileUtils->pathJoin([$baseSavePath, $driveFileChunkEntity->getPath()]);
        }
        unset($deleteChunkEntityList);

        $deleteFileEntityList = $this->driveFileRepository->getAllRecursive(
            $structId,
            $userId,
            false
        );
        foreach ($deleteFileEntityList as $driveFileEntity) {
            if (!$driveFileEntity->isChunk()) {
                $deletePaths[] = $this->fileUtils->pathJoin([$baseSavePath, $driveFileEntity->getPath()]);
            }
        }
        unset($deleteFileEntityList);

        return $deletePaths;
    }
}
