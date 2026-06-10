<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Note;

use App\Layer\Application\DTO\Note\CreateNoteDTO;
use App\Layer\Application\Exception\NoteCategory\NoteCategoryNotFoundException;
use App\Layer\Application\Exception\NoteFile\NoteFileDoesntBelongToUserException;
use App\Layer\Domain\Entity\NoteEntity;
use App\Layer\Domain\Repository\FileNoteLinkRepositoryInterface;
use App\Layer\Domain\Repository\NoteCategoryRepositoryInterface;
use App\Layer\Domain\Repository\NoteFileRepositoryInterface;
use App\Layer\Domain\Repository\NoteRepositoryInterface;
use App\Layer\Domain\Service\Factory\Note\NoteFactory;

final readonly class CreateNoteUseCase
{
    public function __construct(
        private NoteFactory $noteFactory,
        private NoteCategoryRepositoryInterface $noteCategoryRepository,
        private NoteRepositoryInterface $noteRepository,
        private FileNoteLinkRepositoryInterface $fileNoteLinkRepository,
        private NoteFileRepositoryInterface $noteFileRepository,
    ) {}

    /**
     * @throws NoteCategoryNotFoundException
     * @throws NoteFileDoesntBelongToUserException
     */
    public function handle(CreateNoteDTO $in, int $userID): NoteEntity
    {
        $noteCategory = $this->noteCategoryRepository->getById($in->categoryId);
        if (!$noteCategory || $noteCategory->getUserId() !== $userID) {
            throw new NoteCategoryNotFoundException('Категория не найдена');
        }

        $note = $this->noteFactory->getNewNote($in->categoryId, $in->noteBlocks, $in->title);

        $attachedFileIDs = $note->getAttachedFileIDs();
        $checkFileCount = $this->noteFileRepository->getCountByUserAndIDs($userID, $attachedFileIDs);
        if ($checkFileCount !== count($attachedFileIDs)) {
            throw new NoteFileDoesntBelongToUserException('Файл(-ы) в заметке не принадлежат пользователю');
        }

        $note = $this->noteRepository->save(
            $this->noteFactory->getNewNote($in->categoryId, $in->noteBlocks, $in->title)
        );

        $this->fileNoteLinkRepository->upsert($note->getId(), $attachedFileIDs);
        return $note;
    }
}
