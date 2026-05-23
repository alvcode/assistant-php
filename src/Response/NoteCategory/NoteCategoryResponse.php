<?php

declare(strict_types=1);

namespace App\Response\NoteCategory;

use App\Layer\Domain\Entity\NoteCategoryEntity;

final class NoteCategoryResponse
{
    public function __construct(
        public int $id,
        public int $user_id,
        public string $name,
        public ?int $parent_id,
        public int $position,
    ) {}

    public static function fromNoteCategoryEntity(NoteCategoryEntity $noteCategoryEntity): self
    {
        return new self(
            id: $noteCategoryEntity->getId(),
            user_id: $noteCategoryEntity->getUserId(),
            name: $noteCategoryEntity->getName(),
            parent_id: $noteCategoryEntity->getParentId(),
            position: $noteCategoryEntity->getPosition(),
        );
    }

    /** @param NoteCategoryEntity[] $noteCategoryEntities */
    public static function fromNoteCategoryEntities(array $noteCategoryEntities): array
    {
        $result = [];
        foreach ($noteCategoryEntities as $noteCategoryEntity) {
            $result[] = self::fromNoteCategoryEntity($noteCategoryEntity);
        }
        return $result;
    }
}
