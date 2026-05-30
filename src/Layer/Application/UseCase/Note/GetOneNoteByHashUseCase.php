<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Note;

use App\Layer\Application\Exception\Note\NoteNotFoundException;
use App\Layer\Domain\Entity\NoteEntity;
use App\Layer\Domain\Repository\NoteRepositoryInterface;

final readonly class GetOneNoteByHashUseCase
{
    public function __construct(
        private NoteRepositoryInterface $noteRepository,
    ) {}

    /**
     * @throws NoteNotFoundException
     */
    public function handle(string $hash): NoteEntity
    {
        $noteEntity = $this->noteRepository->getByShareHash($hash);
        if (!$noteEntity) {
            throw new NoteNotFoundException('Заметка не найдена');
        }

        return $noteEntity;
    }
}
