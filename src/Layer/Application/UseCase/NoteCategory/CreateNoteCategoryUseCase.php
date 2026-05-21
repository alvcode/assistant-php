<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\NoteCategory;

use App\Layer\Application\DTO\NoteCategory\CreateNoteCategoryDTO;
use App\Layer\Domain\Entity\NoteCategoryEntity;
use App\Layer\Domain\Repository\NoteCategoryRepositoryInterface;

final readonly class CreateNoteCategoryUseCase
{
    public function __construct(
        private NoteCategoryRepositoryInterface $noteCategoryRepository,
    ) {}

    public function handle(CreateNoteCategoryDTO $in): NoteCategoryEntity
    {
        if (!is_null($in->parentId)) {
            $parentNoteCategory = $this->noteCategoryRepository->getById($in->parentId);

        }
    }
}
