<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\NoteFile;

use App\Layer\Application\DTO\Common\FileDTO;
use App\Layer\Application\Exception\NoteFile\NoteFilesystemIsFullException;
use App\Layer\Domain\Entity\NoteFileEntity;
use App\Layer\Domain\Exception\Storage\FailedStorageConfigurationException;
use App\Layer\Domain\Exception\Utils\FailedEncryptionFileException;
use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\DTO\Storage\SaveFileDTO;
use App\Layer\Domain\Repository\NoteFileRepositoryInterface;
use App\Layer\Domain\Service\Factory\NoteFile\NoteFileFactory;
use App\Layer\Domain\Service\Factory\Storage\StorageRepositoryFactoryInterface;
use App\Layer\Domain\Service\Utils\FileUtils;

final readonly class UploadNoteFileUseCase
{
    public function __construct(
        private NoteFileRepositoryInterface $noteFileRepository,
        private ConfigRepositoryInterface $configRepository,
        private FileUtils $fileUtils,
        private StorageRepositoryFactoryInterface $storageRepositoryFactory,
        private NoteFileFactory $noteFileFactory,
    ) {}

    /**
     * @throws NoteFilesystemIsFullException
     * @throws FailedEncryptionFileException
     * @throws FailedStorageConfigurationException|\SodiumException
     */
    public function handle(FileDTO $file, int $userID): NoteFileEntity
    {
        $userSpaceUsed = $this->noteFileRepository->getUsedSpaceByUserID($userID);
        $storageMaxSize = $this->configRepository->getNoteFileStorageLimitPerUser();

        if (($userSpaceUsed->getBytes() + $file->getFile()->getSize()) > $storageMaxSize) {
            throw new NoteFilesystemIsFullException('У пользователя нет места для загрузки файла');
        }

        $newFileName = $this->fileUtils->generateNewFilename($file->getOriginalExtension());
        $lastFileID = $this->noteFileRepository->getLastID();

        $middleFilePath = $this->fileUtils->pathJoin([
            $this->fileUtils->getMiddlePathByFileID($lastFileID + 1),
            $newFileName
        ]);
        $fullFilePath = $this->fileUtils->pathJoin([
            $this->configRepository->getNoteFileSavePath(),
            $middleFilePath
        ]);

        if ($this->configRepository->useFileEncryption()) {
            $fileForSave = $this->fileUtils->encryptFile(
                source: $file->getFile(),
                key: $this->configRepository->getFileEncryptionKey()
            );
        } else {
            $fileForSave = $file->getFile();
        }

        $this->storageRepositoryFactory->getRepository()->save(
            new SaveFileDTO(
                file: $fileForSave,
                savePath: $fullFilePath
            )
        );

        return $this->noteFileRepository->save(
            $this->noteFileFactory->getNewNoteFile(
                userID: $userID,
                originalFilename: $file->getOriginalName(),
                filePath: $middleFilePath,
                ext: $file->getOriginalExtension(),
                sizeInBytes: $fileForSave->getSize(),
            )
        );
    }
}
