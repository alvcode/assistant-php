<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\NoteCategory;

use App\Layer\Application\DTO\NoteCategory\UpdateNoteCategoryDTO;
use App\Layer\Application\Exception\NoteCategory\NoteCategoryNotFoundException;
use App\Layer\Application\Exception\NoteCategory\ParentNoteCategoryNotFoundException;
use App\Layer\Domain\Entity\NoteCategoryEntity;
use App\Layer\Domain\Repository\NoteCategoryRepositoryInterface;
use App\Layer\Domain\Service\NoteCategory\NoteCategoryPositionService;

final readonly class UpdateNoteCategoryUseCase
{
    public function __construct(
        private NoteCategoryRepositoryInterface $noteCategoryRepository,
        private NoteCategoryPositionService $noteCategoryPositionService,
    ) {}

    /**
     * @throws NoteCategoryNotFoundException
     * @throws ParentNoteCategoryNotFoundException
     */
    public function handle(UpdateNoteCategoryDTO $in, int $userID): NoteCategoryEntity
    {
        $noteCategory = $this->noteCategoryRepository->getById($in->id);
        if (!$noteCategory || $noteCategory->getUserId() !== $userID) {
            throw new NoteCategoryNotFoundException('Категория не найдена');
        }

        if ($noteCategory->getParentId() !== $in->parentId) {
            if (!is_null($in->parentId)) {
                $parentNoteCategory = $this->noteCategoryRepository->getById($in->parentId);

                if (!$parentNoteCategory || $parentNoteCategory->getUserId() !== $userID) {
                    throw new ParentNoteCategoryNotFoundException('Родительская категория не найдена');
                }
            }

            $noteCategory->setPosition($this->noteCategoryPositionService->getPositionForNew($userID, $in->parentId));
        }

        $noteCategory->setName($in->name);
        $noteCategory->setParentId($in->parentId);
        return $this->noteCategoryRepository->save($noteCategory);
    }
}
