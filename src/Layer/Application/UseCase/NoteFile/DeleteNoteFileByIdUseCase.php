<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\NoteFile;

use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\NoteFileRepositoryInterface;
use App\Layer\Domain\Service\Factory\Storage\StorageRepositoryFactoryInterface;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;

final readonly class DeleteNoteFileByIdUseCase
{
    public function __construct(
        private NoteFileRepositoryInterface $noteFileRepository,
        private StorageRepositoryFactoryInterface $storageRepositoryFactory,
        private FileUtilsInterface $fileUtils,
        private ConfigRepositoryInterface $configRepository,
    ) {}

    public function handle(int $fileId): void
    {
        $noteFileEntity = $this->noteFileRepository->getById($fileId);
        if (!$noteFileEntity) {
            return;
        }

        $fullFilePath = $this->fileUtils->pathJoin([
            $this->configRepository->getNoteFileSavePath(),
            $noteFileEntity->getFilePath()
        ]);

        $this->storageRepositoryFactory->getRepository()->delete($fullFilePath);
        $this->noteFileRepository->delete($noteFileEntity);
    }
}
