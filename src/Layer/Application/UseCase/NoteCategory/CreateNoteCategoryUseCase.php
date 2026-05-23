<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\NoteCategory;

use App\Layer\Application\DTO\NoteCategory\CreateNoteCategoryDTO;
use App\Layer\Application\Exception\NoteCategory\ParentNoteCategoryNotFoundException;
use App\Layer\Domain\Entity\NoteCategoryEntity;
use App\Layer\Domain\Repository\NoteCategoryRepositoryInterface;
use App\Layer\Domain\Service\NoteCategory\NoteCategoryPositionService;

final readonly class CreateNoteCategoryUseCase
{
    public function __construct(
        private NoteCategoryRepositoryInterface $noteCategoryRepository,
        private NoteCategoryPositionService $noteCategoryPositionService,
    ) {}

    /**
     * @throws ParentNoteCategoryNotFoundException
     */
    public function handle(CreateNoteCategoryDTO $in): NoteCategoryEntity
    {
        if (!is_null($in->parentId)) {
            $parentNoteCategory = $this->noteCategoryRepository->getById($in->parentId);

            if (!$parentNoteCategory || $parentNoteCategory->getUserId() !== $in->userId) {
                throw new ParentNoteCategoryNotFoundException('Родительская категория не найдена');
            }
        }

        return $this->noteCategoryRepository->save(
            new NoteCategoryEntity(
                id: null,
                userId: $in->userId,
                name: $in->name,
                parentId: $in->parentId,
                position: $this->noteCategoryPositionService->getPositionForNew($in->userId, $in->parentId)
            )
        );
    }
}
