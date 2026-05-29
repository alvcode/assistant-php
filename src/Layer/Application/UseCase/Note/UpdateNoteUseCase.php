<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Note;

use App\Layer\Application\DTO\Note\UpdateNoteDTO;
use App\Layer\Application\Exception\Note\NoteNotFoundException;
use App\Layer\Application\Exception\NoteCategory\NoteCategoryNotFoundException;
use App\Layer\Domain\Entity\NoteEntity;
use App\Layer\Domain\Repository\NoteCategoryRepositoryInterface;
use App\Layer\Domain\Repository\NoteRepositoryInterface;
use App\Layer\Domain\Service\Factory\Note\NoteFactory;

final readonly class UpdateNoteUseCase
{
    public function __construct(
        private NoteRepositoryInterface $noteRepository,
        private NoteCategoryRepositoryInterface $noteCategoryRepository,
        private NoteFactory $noteFactory,
    ) {}

    /**
     * @throws NoteNotFoundException
     * @throws NoteCategoryNotFoundException
     */
    public function handle(UpdateNoteDTO $in, int $userID): NoteEntity
    {
        $noteEntity = $this->noteRepository->getByID($in->id);
        if (!$noteEntity) {
            throw new NoteNotFoundException('Заметка не найдена');
        }

        $noteCategoryEntity = $this->noteCategoryRepository->getById($noteEntity->getCategoryId());
        if (!$noteCategoryEntity || $noteCategoryEntity->getUserId() !== $userID) {
            throw new NoteCategoryNotFoundException('Текущая категория не найдена');
        }

        if ($noteEntity->getCategoryId() !== $in->categoryId) {
            $newCategoryEntity = $this->noteCategoryRepository->getByID($in->categoryId);
            if (!$newCategoryEntity || $newCategoryEntity->getUserId() !== $userID) {
                throw new NoteCategoryNotFoundException('Новая категория не найдена');
            }
        }

        return $this->noteRepository->save(
            $this->noteFactory->getUpdatedNote($noteEntity, $in->noteBlocks, $in->categoryId, $in->title)
        );
    }
}
