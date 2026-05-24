<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\NoteCategory;

use App\Layer\Application\Exception\NoteCategory\NoteCategoryNotFoundException;
use App\Layer\Domain\Exception\NoteCategory\NoteCategoryAlreadyFirstPositionException;
use App\Layer\Domain\Repository\NoteCategoryRepositoryInterface;
use App\Layer\Domain\Service\NoteCategory\NoteCategoryPositionService;

final readonly class NoteCategoryPositionUpUseCase
{
    public function __construct(
        private NoteCategoryRepositoryInterface $noteCategoryRepository,
        private NoteCategoryPositionService $noteCategoryPositionService,
    ) {}

    /**
     * @throws NoteCategoryNotFoundException
     * @throws NoteCategoryAlreadyFirstPositionException
     */
    public function handle(int $id, int $userID): void
    {
        $noteCategoryEntity = $this->noteCategoryRepository->getById($id);
        if (!$noteCategoryEntity || $noteCategoryEntity->getUserId() !== $userID) {
            throw new NoteCategoryNotFoundException('Категория не найдена');
        }

        $this->noteCategoryPositionService->positionUp($id, $userID);
    }
}
