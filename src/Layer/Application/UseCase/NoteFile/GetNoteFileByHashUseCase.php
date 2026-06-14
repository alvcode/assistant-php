<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\NoteFile;

use App\Layer\Application\DTO\Common\FileDTO;
use App\Layer\Application\Exception\NoteFile\NoteFileNotFoundByHashException;
use App\Layer\Domain\Exception\Storage\FailedStorageConfigurationException;
use App\Layer\Domain\Exception\Utils\FailedDecryptionFileException;
use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\NoteFileRepositoryInterface;
use App\Layer\Domain\Service\Factory\Storage\StorageRepositoryFactoryInterface;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;

final readonly class GetNoteFileByHashUseCase
{
    public function __construct(
        private NoteFileRepositoryInterface $noteFileRepository,
        private ConfigRepositoryInterface $configRepository,
        private FileUtilsInterface $fileUtils,
        private StorageRepositoryFactoryInterface $storageRepositoryFactory,
    ) {}

    /**
     * @throws NoteFileNotFoundByHashException
     * @throws FailedDecryptionFileException
     * @throws FailedStorageConfigurationException
     */
    public function handle(string $hash): FileDTO
    {
        $noteFileEntity = $this->noteFileRepository->getByHash($hash);
        if (!$noteFileEntity) {
            throw new NoteFileNotFoundByHashException('Файл не найден');
        }

        $fullFilePath = $this->fileUtils->pathJoin([
            $this->configRepository->getNoteFileSavePath(),
            $noteFileEntity->getFilePath()
        ]);

        if ($this->configRepository->useFileEncryption()) {
            $file = $this->fileUtils->decryptFile(
                source: $this->storageRepositoryFactory->getRepository()->getFile($fullFilePath),
                key: $this->configRepository->getFileEncryptionKey()
            );
        } else {
            $file = $this->storageRepositoryFactory->getRepository()->getFile($fullFilePath);
        }

        return new FileDTO(
            file: $file,
            originalExtension: $noteFileEntity->getExt(),
            originalName: $noteFileEntity->getOriginalFilename()
        );
    }
}
