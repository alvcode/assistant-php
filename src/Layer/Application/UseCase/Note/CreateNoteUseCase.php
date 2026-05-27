<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Note;

use App\Layer\Application\DTO\Note\CreateNoteDTO;
use App\Layer\Application\Exception\NoteCategory\NoteCategoryNotFoundException;
use App\Layer\Domain\Entity\NoteEntity;
use App\Layer\Domain\Repository\FileNoteLinkRepositoryInterface;
use App\Layer\Domain\Repository\NoteCategoryRepositoryInterface;
use App\Layer\Domain\Repository\NoteRepositoryInterface;
use App\Layer\Domain\Service\Factory\Note\NoteFactory;

final readonly class CreateNoteUseCase
{
    public function __construct(
        private NoteFactory $noteFactory,
        private NoteCategoryRepositoryInterface $noteCategoryRepository,
        private NoteRepositoryInterface $noteRepository,
        private FileNoteLinkRepositoryInterface $fileNoteLinkRepository,
    ) {}

    /**
     * @throws NoteCategoryNotFoundException
     */
    public function handle(CreateNoteDTO $in, int $userID): NoteEntity
    {
        $noteCategory = $this->noteCategoryRepository->getById($in->categoryId);
        if (!$noteCategory || $noteCategory->getUserId() !== $userID) {
            throw new NoteCategoryNotFoundException('Категория не найдена');
        }

        $note = $this->noteRepository->save(
            $this->noteFactory->getNewNote($in->categoryId, $in->noteBlocks, $in->title)
        );

        // TODO: дыра. нужно проверять ID файлов на принадлежность юзеру
        $this->fileNoteLinkRepository->upsert($note->getId(), $note->getAttachedFileIDs());

        return $note;
    }
}
