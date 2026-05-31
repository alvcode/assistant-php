<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Note;

use App\Layer\Application\Exception\Note\NoteFailedToGenerateUniqueHashException;
use App\Layer\Application\Exception\Note\NoteNotFoundException;
use App\Layer\Application\Exception\Note\NoteShareExistsException;
use App\Layer\Application\Exception\Note\NoteShareNotFoundException;
use App\Layer\Domain\Entity\NoteShareEntity;
use App\Layer\Domain\Repository\NoteRepositoryInterface;
use App\Layer\Domain\Repository\NoteShareHashesRepositoryInterface;
use App\Layer\Domain\Service\Utils\HasherServiceInterface;

final readonly class ShareNoteUseCase
{
    public function __construct(
        private NoteRepositoryInterface $noteRepository,
        private NoteShareHashesRepositoryInterface $noteShareHashesRepository,
        private HasherServiceInterface $hasherService,
    ) {}

    /**
     * @throws NoteNotFoundException
     * @throws NoteShareExistsException
     * @throws NoteFailedToGenerateUniqueHashException
     */
    public function create(int $noteID, int $userID): NoteShareEntity
    {
        $noteBelongsUser = $this->noteRepository->isBelongToUser($noteID, $userID);
        if (!$noteBelongsUser) {
            throw new NoteNotFoundException('Заметка не найдена');
        }

        $existsShare = $this->noteShareHashesRepository->existsByNoteID($noteID);
        if ($existsShare) {
            throw new NoteShareExistsException('Заметкой уже поделились');
        }

        $hash = null;
        for ($i = 0; $i < 20; ++$i) {
            $h = $this->hasherService->generateRandomStringWithoutSymbols(80);
            $existsByHash = $this->noteShareHashesRepository->existsByHash($h);
            if (!$existsByHash) {
                $hash = $h;
                break;
            }
        }

        if (!$hash) {
            throw new NoteFailedToGenerateUniqueHashException('Не удалось сгенерировать уникальный хэш');
        }

        return $this->noteShareHashesRepository->save(
            new NoteShareEntity(id: null, noteID: $noteID, hash: $hash)
        );
    }

    /**
     * @throws NoteNotFoundException
     * @throws NoteShareNotFoundException
     */
    public function getOne(int $noteID, int $userID): NoteShareEntity
    {
        $noteBelongsUser = $this->noteRepository->isBelongToUser($noteID, $userID);
        if (!$noteBelongsUser) {
            throw new NoteNotFoundException('Заметка не найдена');
        }

        $noteShareEntity = $this->noteShareHashesRepository->getByNoteID($noteID);
        if (!$noteShareEntity) {
            throw new NoteShareNotFoundException('share-ссылка не найдена');
        }
        return $noteShareEntity;
    }
}
