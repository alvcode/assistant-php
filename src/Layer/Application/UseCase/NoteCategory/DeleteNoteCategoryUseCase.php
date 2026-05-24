<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\NoteCategory;

use App\Layer\Application\Exception\NoteCategory\CategoryHasNotesException;
use App\Layer\Application\Exception\NoteCategory\NoteCategoryNotFoundException;
use App\Layer\Domain\Entity\NoteCategoryEntity;
use App\Layer\Domain\Repository\NoteCategoryRepositoryInterface;
use App\Layer\Domain\Repository\NoteRepositoryInterface;

final readonly class DeleteNoteCategoryUseCase
{
    public function __construct(
        private NoteCategoryRepositoryInterface $noteCategoryRepository,
        private NoteRepositoryInterface $noteRepository,
    ) {}

    /**
     * @throws NoteCategoryNotFoundException
     * @throws CategoryHasNotesException
     */
    public function handle(int $id, int $userId): void
    {
        $categories = $this->noteCategoryRepository->getByIDAndUserWithChildren($id, $userId);
        if (empty($categories)) {
            throw new NoteCategoryNotFoundException('Категория не найдена');
        }

        $categoryIDs = array_map(
            function(NoteCategoryEntity $entity): int {
                return $entity->getId();
            },
            $categories
        );

        $noteExists = $this->noteRepository->checkExistsByCategoryIDs($categoryIDs);

        if ($noteExists) {
            throw new CategoryHasNotesException('У категории есть заметки');
        }

        $this->noteCategoryRepository->deleteByIDs($categoryIDs);
    }
}
