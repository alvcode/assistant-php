<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Note;

use App\Layer\Application\Exception\NoteCategory\NoteCategoryNotFoundException;
use App\Layer\Domain\Entity\Aggregate\NoteListAggregate;
use App\Layer\Domain\Entity\NoteCategoryEntity;
use App\Layer\Domain\Repository\NoteCategoryRepositoryInterface;
use App\Layer\Domain\Repository\NoteRepositoryInterface;

final readonly class GetAllNotesByCategoryUseCase
{
    public function __construct(
        private NoteCategoryRepositoryInterface $noteCategoryRepository,
        private NoteRepositoryInterface $noteRepository,
    ) {}

    /**
     * @throws NoteCategoryNotFoundException
     * @return NoteListAggregate[]
     */
    public function handle(int $categoryID, int $userID): array
    {
        $categories = $this->noteCategoryRepository->getByIDAndUserWithChildren($categoryID, $userID);

        if (empty($categories)) {
            throw new NoteCategoryNotFoundException('Категория не найдена');
        }

        return $this->noteRepository->getListByCategoryIds(
            array_map(fn(NoteCategoryEntity $categoryEntity) => $categoryEntity->getId(), $categories)
        );
    }
}
