<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Note;

use App\Layer\Application\Exception\Note\NoteNotFoundException;
use App\Layer\Domain\Repository\FileNoteLinkRepositoryInterface;
use App\Layer\Domain\Repository\NoteCategoryRepositoryInterface;
use App\Layer\Domain\Repository\NoteRepositoryInterface;

final readonly class DeleteNoteUseCase
{
    public function __construct(
        private NoteRepositoryInterface $noteRepository,
        private NoteCategoryRepositoryInterface $noteCategoryRepository,
        private FileNoteLinkRepositoryInterface $fileNoteLinkRepository,
    ) {}

    /**
     * @throws NoteNotFoundException
     */
    public function handle(int $noteID, int $userID): void
    {
        $noteEntity = $this->noteRepository->getByID($noteID);
        if (!$noteEntity) {
            throw new NoteNotFoundException('Заметка не найдена');
        }

        $noteCategoryEntity = $this->noteCategoryRepository->getById($noteEntity->getCategoryId());
        if (!$noteCategoryEntity || $noteCategoryEntity->getUserId() !== $userID) {
            throw new NoteNotFoundException('Заметка не найдена');
        }

        $this->noteRepository->delete($noteEntity);
        $this->fileNoteLinkRepository->deleteByNoteID($noteID);
    }
}
