<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\NoteCategory;

use App\Layer\Domain\Entity\NoteCategoryEntity;
use App\Layer\Domain\Exception\NoteCategory\NoteCategoryAlreadyFirstPositionException;
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

    /**
     * @throws NoteCategoryAlreadyFirstPositionException
     */
    public function positionUp(int $id, int $userID): void
    {
        $categories = $this->noteCategoryRepository->getAllByUserId($userID);
        if (empty($categories)) {
            return;
        }

        /** @var array<int,array<int,NoteCategoryEntity>> $grouped */
        $grouped = [];
        $needUpCategoryKey = 0;

        // Группировка по parent_id
        foreach ($categories as $categoryEntity) {
            $key = $categoryEntity->getParentId() ?? 0;

            $grouped[$key][] = $categoryEntity;

            if ($categoryEntity->getId() === $id) {
                $needUpCategoryKey = $key;
            }
        }

        // Сортировка внутри группы по position
        foreach ($grouped as $key => $group) {
            usort($group, fn($a, $b) => $a->getPosition() <=> $b->getPosition());
            $grouped[$key] = $group;
        }

        if ($grouped[$needUpCategoryKey][0]->getId() === $id) {
            throw new NoteCategoryAlreadyFirstPositionException();
        }

        // Меняем местами
        foreach ($grouped[$needUpCategoryKey] as $i => $category) {
            if ($category->getId() === $id) {
                $prev = $grouped[$needUpCategoryKey][$i - 1];

                $grouped[$needUpCategoryKey][$i - 1] = $grouped[$needUpCategoryKey][$i];
                $grouped[$needUpCategoryKey][$i] = $prev;

                break;
            }
        }

        // Пересчет позиций
        foreach ($grouped as $group) {
            foreach ($group as $i => $categoryEntity) {
                $newPosition = $i + 1;

                if ($categoryEntity->getPosition() !== $newPosition) {
                    $categoryEntity->setPosition($newPosition);
                    $this->noteCategoryRepository->save($categoryEntity);
                }
            }
        }
    }
}
