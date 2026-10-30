<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveShare;

use App\Layer\Application\Exception\Drive\DriveShareNotFoundException;
use App\Layer\Domain\Entity\DriveStructEntity;
use App\Layer\Domain\Repository\DriveShareHashesRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;

final readonly class DriveShareGetStructByHashUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
        private DriveShareHashesRepositoryInterface $shareHashesRepository,
    ) {}

    /**
     * @throws DriveShareNotFoundException
     */
    public function handle(string $hash): DriveStructEntity
    {
        $driveShareEntity = $this->shareHashesRepository->getByHash($hash);
        if (!$driveShareEntity) {
            throw new DriveShareNotFoundException('Share-запись не найдена');
        }

        $driveStructEntity = $this->driveStructRepository->getById(
            $driveShareEntity->getStructId(),
            false
        );
        if (!$driveStructEntity) {
            throw new DriveShareNotFoundException('Структура не найдена');
        }

        return $driveStructEntity;
    }
}
