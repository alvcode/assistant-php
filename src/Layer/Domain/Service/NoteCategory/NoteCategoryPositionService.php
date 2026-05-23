<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\NoteCategory;

use App\Layer\Domain\Repository\NoteCategoryRepositoryInterface;

final readonly class NoteCategoryPositionService
{
    public function __construct(
        private NoteCategoryRepositoryInterface $noteCategoryRepository,
    ) {}

    public function getPositionForNew(int $userId, ?int $parentCategoryId): int
    {
        $maxPosition = $this->noteCategoryRepository->getMaxPosition($userId, $parentCategoryId);
        return $maxPosition + 1;
    }
}
