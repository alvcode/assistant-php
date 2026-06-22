<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\NoteFile;

use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\DTO\Storage\SaveFileDTO;
use App\Layer\Domain\Repository\NoteFileRepositoryInterface;
use App\Layer\Domain\Service\Factory\Storage\StorageRepositoryFactoryInterface;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;

final readonly class NoteFileRelocationStorageUseCase
{
    public function __construct(
        private NoteFileRepositoryInterface $noteFileRepository,
        private StorageRepositoryFactoryInterface $storageRepositoryFactory,
        private ConfigRepositoryInterface $configRepository,
        private FileUtilsInterface $fileUtils,
    ) {}

    public function handle(bool $toLocal): void
    {
        $localStorageRepository = $this->storageRepositoryFactory->getLocalStorage();
        $s3StorageRepository = $this->storageRepositoryFactory->getS3Storage();

        foreach ($this->noteFileRepository->getAllFiles() as $noteFileEntity) {
            $fullFilePath = $this->fileUtils->pathJoin([
                $this->configRepository->getNoteFileSavePath(),
                $noteFileEntity->getFilePath()
            ]);

            if ($toLocal) {
                if ($localStorageRepository->isExists($fullFilePath)) {
                    continue;
                }
                $oldFile = $s3StorageRepository->getFile($fullFilePath);
                $localStorageRepository->save(new SaveFileDTO(file: $oldFile, savePath: $fullFilePath));
                $s3StorageRepository->delete($fullFilePath);
            } else {
                if ($s3StorageRepository->isExists($fullFilePath)) {
                    continue;
                }
                $oldFile = $localStorageRepository->getFile($fullFilePath);
                $s3StorageRepository->save(new SaveFileDTO(file: $oldFile, savePath: $fullFilePath));
                $localStorageRepository->delete($fullFilePath);
            }
        }
    }
}
