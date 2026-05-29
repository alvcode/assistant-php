<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Factory\Note;

use App\Layer\Domain\Entity\NoteEntity;
use App\Layer\Domain\Service\Utils\DateTime;
use App\Layer\Domain\Service\Utils\DateTimeImmutable;
use App\Layer\Domain\Service\Utils\StringUtilsInterface;

final readonly class NoteFactory
{
    public function __construct(
        private StringUtilsInterface $stringUtils,
    ) {}

    public function getNewNote(int $categoryId, array $noteBlocks, ?string $title): NoteEntity
    {
        return new NoteEntity(
            id: null,
            categoryId: $categoryId,
            noteBlocks: $noteBlocks,
            createdAt: DateTimeImmutable::createNowUtc(),
            updatedAt: DateTime::createNowUtc(),
            title: $this->getTitle($noteBlocks, $title),
            pinned: false,
        );
    }

    public function getUpdatedNote(NoteEntity $entity, array $noteBlocks, int $categoryID, ?string $title): NoteEntity
    {
        $entity->setNoteBlocks($noteBlocks);
        $entity->setCategoryId($categoryID);
        $entity->setTitle($this->getTitle($noteBlocks, $title));
        $entity->setUpdatedAt(DateTime::createNowUtc());
        return $entity;
    }

    private function getTitle(array $noteBlocks, ?string $title): ?string
    {
        $result = '';
        if (!empty($title)) {
            $result = trim($this->stringUtils->truncateString($title, 150));
        } else {
            if (!empty($noteBlocks) && isset($noteBlocks[0]['data']['text'])) {
                foreach ($noteBlocks as $noteBlock) {
                    if (isset($noteBlock['data']['text'])) {
                        $result = trim(
                            $this->stringUtils->truncateString(
                                $this->stringUtils->removeHtmlTags($noteBlock['data']['text']),
                                150
                            )
                        );
                        break;
                    }
                }
            }
        }

        return !empty($result) ? $result : null;
    }
}
