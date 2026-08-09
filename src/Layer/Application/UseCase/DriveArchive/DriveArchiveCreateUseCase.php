<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveArchive;

use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Application\Exception\DriveArchive\DriveArchiveJobNotFoundException;
use App\Layer\Domain\Dict\Drive\DriveArchiveJobStatusEnum;
use App\Layer\Domain\Dict\Drive\DriveStructTypeEnum;
use App\Layer\Domain\Entity\DriveArchiveJobEntity;
use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\DriveArchiveRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Repository\QueueRepositoryInterface;
use App\Layer\Domain\Service\Drive\DriveAssembleChunkedFileService;
use App\Layer\Domain\Service\Drive\GetRecursiveFileStructsWithRealPath;
use App\Layer\Domain\Service\Factory\Drive\DriveArchiveFactory;
use App\Layer\Domain\Service\Factory\Storage\StorageRepositoryFactoryInterface;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;
use App\Layer\Domain\Repository\DTO\Storage\SaveFileDTO;
use Exception;

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
        private GetRecursiveFileStructsWithRealPath $getRecursiveFileStructsWithRealPath,
        private DriveFileRepositoryInterface $driveFileRepository,
        private DriveAssembleChunkedFileService $driveAssembleChunkedFileService,
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

        try {
            foreach ($driveArchiveJobEntity->getStructIds() as $structId) {
                foreach (
                    $this->getRecursiveFileStructsWithRealPath->service(
                        $driveArchiveJobEntity->getUserId(),
                        $structId,
                        $driveArchiveJobEntity->getBaseSavePath($this->fileUtils, $this->configRepository)
                    ) as $driveStructWithRealPath
                ) {
                    if (in_array($driveStructWithRealPath->driveStructEntity->getId(), $processedStructIds, true)) {
                        continue;
                    }

                    $driveFileEntity = $this->driveFileRepository->getByStructId(
                        $driveStructWithRealPath->driveStructEntity->getId()
                    );
                    if (!$driveFileEntity) {
                        continue;
                    }

                    if ($driveFileEntity->isChunk()) {
                        $file = $this->driveAssembleChunkedFileService->handle(
                            $driveStructWithRealPath->driveStructEntity->getId(),
                            $driveArchiveJobEntity->getUserId()
                        );
                    } else {
                        $fullFilePath = $this->fileUtils->pathJoin([
                            $this->configRepository->getDriveFileSavePath(),
                            $driveFileEntity->getPath()
                        ]);
                        $file = $this->storageRepositoryFactory->getRepository()->getFile($fullFilePath);
                        if ($this->configRepository->useFileEncryption()) {
                            $file = $this->fileUtils->decryptFile(
                                source: $file,
                                key: $this->configRepository->getFileEncryptionKey()
                            );
                        }
                    }

                    $this->storageRepositoryFactory->getLocalStorage()->save(
                        new SaveFileDTO(
                            file: $file,
                            savePath: $driveStructWithRealPath->realPath->getPath()
                        )
                    );

                    $processedStructIds[] = $driveStructWithRealPath->driveStructEntity->getId();
                }
            }
        } catch (Exception $e) {
            $this->storageRepositoryFactory->getLocalStorage()->delete(
                $driveArchiveJobEntity->getBaseSavePath($this->fileUtils, $this->configRepository)
            );
            $driveArchiveJobEntity->setErrorDescription($e->getMessage());
            $driveArchiveJobEntity->setStatus(DriveArchiveJobStatusEnum::Failed);
            $this->driveArchiveRepository->save($driveArchiveJobEntity);
        }

        /**
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
