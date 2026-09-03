<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveArchive;

use App\Layer\Application\Exception\DriveArchive\DriveArchiveJobNotFoundException;
use App\Layer\Domain\Dict\Common\FileSizeTypeEnum;
use App\Layer\Domain\Entity\DriveArchiveFileChunkEntity;
use App\Layer\Domain\Entity\DriveArchiveFileEntity;
use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\DriveArchiveFileChunkRepositoryInterface;
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
use App\Layer\Domain\ValueObject\FileSizeVO;
use Exception;
use SplFileInfo;

final readonly class DriveArchiveCreateUseCase
{
    private const ARCHIVE_CHUNK_SIZE_BYTES = 50 * 1024 * 1024;
    private const STREAM_CHUNK_SIZE = 1024 * 1024;

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
        private DriveArchiveFileChunkRepositoryInterface $driveArchiveFileChunkRepository,
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
            $workDirectoryPath = $driveArchiveJobEntity->getBaseSavePath($this->fileUtils, $this->configRepository);

            foreach ($driveArchiveJobEntity->getStructIds() as $structId) {
                foreach (
                    $this->getRecursiveFileStructsWithRealPath->service(
                        $driveArchiveJobEntity->getUserId(),
                        $structId,
                        $workDirectoryPath
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
                            $fileDecrypted = $this->fileUtils->decryptFile(
                                source: $file->getFile(),
                                key: $this->configRepository->getFileEncryptionKey()
                            );
                            $file->unlinkIfTemporary();
                            $file = $fileDecrypted;
                        }
                    }

                    $this->storageRepositoryFactory->getLocalStorage()->save(
                        new SaveFileDTO(
                            file: $file->getFile(),
                            savePath: $driveStructWithRealPath->realPath->getPath()
                        )
                    );
                    $file->unlinkIfTemporary();

                    $processedStructIds[] = $driveStructWithRealPath->driveStructEntity->getId();
                }
            }
        } catch (Exception $e) {
            $this->storageRepositoryFactory->getLocalStorage()->delete(
                $driveArchiveJobEntity->getBaseSavePath($this->fileUtils, $this->configRepository)
            );
            $driveArchiveJobEntity->setFailed($e->getMessage());
            $this->driveArchiveRepository->save($driveArchiveJobEntity);
            throw $e;
        }

        $archivePath = $driveArchiveJobEntity->getPathToArchive($this->fileUtils, $this->configRepository);

        $this->fileUtils->createArchive(
            sourcePath: $workDirectoryPath,
            destinationPath: $archivePath
        );

        $driveArchiveFileEntity = $this->driveArchiveRepository->saveFileEntity(
            $this->driveArchiveFactory->getNewDriveArchiveFile($driveArchiveJobEntity->getId())
        );

        $this->fileUtils->unlinkPath($workDirectoryPath);

        $totalSize = $this->splitArchiveIntoChunks($archivePath, $driveArchiveFileEntity);

        $driveArchiveFileEntity->setSize(new FileSizeVO(size: $totalSize, sizeType: FileSizeTypeEnum::Bytes));
        $this->driveArchiveRepository->saveFileEntity($driveArchiveFileEntity);

        $this->fileUtils->unlinkPath($archivePath);

        $driveArchiveJobEntity->setSuccess();
        $this->driveArchiveRepository->save($driveArchiveJobEntity);
    }

    private function splitArchiveIntoChunks(string $archivePath, DriveArchiveFileEntity $driveArchiveFileEntity): int
    {
        $driveArchiveFileId = $driveArchiveFileEntity->getId();
        if ($driveArchiveFileId === null) {
            throw new Exception('Идентификатор файла архива не задан');
        }

        $input = fopen($archivePath, 'rb');
        if ($input === false) {
            throw new Exception('Не удалось открыть архив для чтения');
        }

        $chunkNumber = 1;
        $totalSize = 0;

        try {
            while (!feof($input)) {
                $chunkTempPath = $this->fileUtils->createTempFile();
                $output = fopen($chunkTempPath, 'wb');
                if ($output === false) {
                    throw new Exception('Не удалось создать чанк архива');
                }

                $chunkSize = 0;
                try {
                    while ($chunkSize < self::ARCHIVE_CHUNK_SIZE_BYTES && !feof($input)) {
                        $data = fread($input, self::STREAM_CHUNK_SIZE);
                        if ($data === false) {
                            throw new Exception('Не удалось прочитать архив');
                        }
                        if ($data === '') {
                            break;
                        }
                        fwrite($output, $data);
                        $chunkSize += strlen($data);
                    }
                } finally {
                    fclose($output);
                }

                if ($chunkSize === 0) {
                    unlink($chunkTempPath);
                    break;
                }

                $chunkSavePath = $this->fileUtils->pathJoin([
                    $this->configRepository->getTempSavePath(),
                    'archives',
                    $driveArchiveFileId . '_chunks',
                    $this->fileUtils->generateNewFilename(sprintf('archive_part_%d', $chunkNumber)),
                ]);

                $this->storageRepositoryFactory->getLocalStorage()->save(
                    new SaveFileDTO(
                        file: new SplFileInfo($chunkTempPath),
                        savePath: $chunkSavePath,
                    )
                );
                unlink($chunkTempPath);

                $this->driveArchiveFileChunkRepository->save(
                    new DriveArchiveFileChunkEntity(
                        id: null,
                        driveArchiveFileId: $driveArchiveFileId,
                        path: $chunkSavePath,
                        size: new FileSizeVO(size: $chunkSize, sizeType: FileSizeTypeEnum::Bytes),
                        chunkNumber: $chunkNumber,
                    )
                );

                $totalSize += $chunkSize;
                $chunkNumber++;
            }
        } finally {
            fclose($input);
        }

        return $totalSize;
    }
}
