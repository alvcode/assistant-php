<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Drive;

use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileRepositoryInterface;
use App\Layer\Domain\Repository\DTO\Storage\SaveFileDTO;
use App\Layer\Domain\Service\Factory\Storage\StorageRepositoryFactoryInterface;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;
use Exception;

/**
 * Принимает на вход массив ID структур и путь для сохранения. Воссоздает эти структуры в реальной папке $savePath
 */
final readonly class CreationFolderStructService
{
    public function __construct(
        private GetRecursiveFileStructsWithRealPath $getRecursiveFileStructsWithRealPath,
        private DriveFileRepositoryInterface $driveFileRepository,
        private DriveAssembleChunkedFileService $driveAssembleChunkedFileService,
        private FileUtilsInterface $fileUtils,
        private ConfigRepositoryInterface $configRepository,
        private StorageRepositoryFactoryInterface $storageRepositoryFactory,
    ) {}

    /**
     * @param int[] $structIds
     * @param int $userId
     * @param string $savePath
     * @return void
     * @throws Exception
     */
    public function handle(array $structIds, int $userId, string $savePath): void
    {
        $processedStructIds = [];

        try {
            foreach ($structIds as $structId) {
                foreach (
                    $this->getRecursiveFileStructsWithRealPath->service(
                        $userId,
                        $structId,
                        $savePath
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
                            $userId
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
            $this->storageRepositoryFactory->getLocalStorage()->delete($savePath);
            throw $e;
        }
    }
}
