<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\NoteFile;

use App\Layer\Application\DTO\Common\FileDTO;
use App\Layer\Application\Exception\NoteFile\NoteFilesystemIsFullException;
use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\NoteFileRepositoryInterface;
use App\Layer\Domain\Service\Utils\FileUtils;

final readonly class UploadNoteFileUseCase
{
    public function __construct(
        private NoteFileRepositoryInterface $noteFileRepository,
        private ConfigRepositoryInterface $configRepository,
        private FileUtils $fileUtils,
    ) {}

    /**
     * @throws NoteFilesystemIsFullException
     */
    public function handle(FileDTO $file, int $userID): void
    {
        $userSpaceUsed = $this->noteFileRepository->getUsedSpaceByUserID($userID);
        $storageMaxSize = $this->configRepository->getNoteFileStorageLimitPerUser();

        if (($userSpaceUsed->getBytes() + $file->getFile()->getSize()) > $storageMaxSize) {
            throw new NoteFilesystemIsFullException('У пользователя нет места для загрузки файла');
        }

        $newFileName = $this->fileUtils->generateNewFilename($file->getOriginalExtension());
        $lastFileID = $this->noteFileRepository->getLastID();

        $middleFilePath = $this->fileUtils->pathJoin(
            $this->fileUtils->getMiddlePathByFileID($lastFileID + 1),
            $newFileName
        );
        $fullFilePath = $this->fileUtils->pathJoin(
            $this->configRepository->getNoteFileSavePath(),
            $middleFilePath
        );


        dd($fullFilePath);
    }
}
