<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Note;

use App\Layer\Application\Exception\Note\NoteNotFoundException;
use App\Layer\Domain\Entity\NoteShareEntity;
use App\Layer\Domain\Repository\NoteRepositoryInterface;

final readonly class ShareNoteUseCase
{
    public function __construct(
        private NoteRepositoryInterface $noteRepository,
    ) {}

    /**
     * @throws NoteNotFoundException
     */
    public function create(int $noteID, int $userID): NoteShareEntity
    {
        $noteBelongsUser = $this->noteRepository->isBelongToUser($noteID, $userID);
        if (!$noteBelongsUser) {
            throw new NoteNotFoundException('Заметка не найдена');
        }
    }
}
