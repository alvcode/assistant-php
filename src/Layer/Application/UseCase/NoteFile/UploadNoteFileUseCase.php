<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\NoteFile;

use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\NoteFileRepositoryInterface;
use SplFileInfo;

final readonly class UploadNoteFileUseCase
{
    public function __construct(
        private NoteFileRepositoryInterface $noteFileRepository,
        private ConfigRepositoryInterface $configRepository,
    ) {}

    public function handle(SplFileInfo $file, int $userID): void
    {
        $userSpaceUsed = $this->noteFileRepository->getUsedSpaceByUserID($userID);
        $storageMaxSize = $this->configRepository->getNoteFileStorageLimitPerUser();

        if (($userSpaceUsed->getBytes() + $file->getSize()) > $storageMaxSize) {

        }
    }
}
