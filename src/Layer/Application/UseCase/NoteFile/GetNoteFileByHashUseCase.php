<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\NoteFile;

use App\Layer\Application\DTO\NoteFile\GetNoteFileDTO;
use App\Layer\Application\Exception\NoteFile\NoteFileNotFoundByHashException;
use App\Layer\Domain\Exception\Utils\FailedDecryptionFileException;
use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\NoteFileRepositoryInterface;
use App\Layer\Domain\Repository\StorageRepositoryInterface;
use App\Layer\Domain\Service\Utils\FileUtils;

final readonly class GetNoteFileByHashUseCase
{
    public function __construct(
        private NoteFileRepositoryInterface $noteFileRepository,
        private ConfigRepositoryInterface $configRepository,
        private FileUtils $fileUtils,
        private StorageRepositoryInterface $storageRepository,
    ) {}

    /**
     * @throws NoteFileNotFoundByHashException
     * @throws FailedDecryptionFileException
     */
    public function handle(string $hash): GetNoteFileDTO
    {
        $noteFileEntity = $this->noteFileRepository->getByHash($hash);
        if (!$noteFileEntity) {
            throw new NoteFileNotFoundByHashException('Файл не найден');
        }

        $fullFilePath = $this->fileUtils->pathJoin([
            $this->configRepository->getProjectDir(),
            $this->configRepository->getNoteFileSavePath(),
            $noteFileEntity->getFilePath()
        ], true);

        if ($this->configRepository->useFileEncryption()) {
            $file = $this->fileUtils->decryptFile(
                source: $this->storageRepository->getFile($fullFilePath),
                key: $this->configRepository->getFileEncryptionKey()
            );
        } else {
            $file = $this->storageRepository->getFile($fullFilePath);
        }

        return new GetNoteFileDTO(
            file: $file,
            originalFileName: $noteFileEntity->getOriginalFilename(),
        );
    }
}
