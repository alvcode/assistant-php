<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\NoteCategory;

use App\Layer\Domain\Entity\NoteCategoryEntity;
use App\Layer\Domain\Repository\NoteCategoryRepositoryInterface;

final readonly class ListNoteCategoryUseCase
{
    public function __construct(
        private NoteCategoryRepositoryInterface $noteCategoryRepository,
    ) {}

    /** @return NoteCategoryEntity[] */
    public function handle(int $userId): array
    {
        return $this->noteCategoryRepository->getAllByUserId($userId);
    }
}
