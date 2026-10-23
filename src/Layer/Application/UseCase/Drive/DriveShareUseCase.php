<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Drive;

use App\Layer\Application\Exception\Common\FailedToGenerateUniqueHashException;
use App\Layer\Application\Exception\Common\ShareLinkNotFoundException;
use App\Layer\Application\Exception\Drive\DriveShareExistsException;
use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Domain\Entity\DriveShareEntity;
use App\Layer\Domain\Repository\DriveShareHashesRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Service\Utils\HasherServiceInterface;

final readonly class DriveShareUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
        private DriveShareHashesRepositoryInterface $shareHashesRepository,
        private HasherServiceInterface $hasherService,
    )
    {
    }

    /**
     * @throws DriveStructNotFoundException
     * @throws DriveShareExistsException
     * @throws FailedToGenerateUniqueHashException
     */
    public function create(int $structID, int $userID): DriveShareEntity
    {
        $this->assertBelongToUser($structID, $userID);

        $existsShare = $this->shareHashesRepository->existsByStructId($structID);
        if ($existsShare) {
            throw new DriveShareExistsException('Структурой уже поделились');
        }

        $hash = null;
        for ($i = 0; $i < 20; ++$i) {
            $h = $this->hasherService->generateRandomStringWithoutSymbols(80);
            $existsByHash = $this->shareHashesRepository->existsByHash($h);
            if (!$existsByHash) {
                $hash = $h;
                break;
            }
        }

        if (!$hash) {
            throw new FailedToGenerateUniqueHashException('Не удалось сгенерировать уникальный хэш');
        }

        return $this->shareHashesRepository->save(
            new DriveShareEntity(id: null, structId: $structID, hash: $hash)
        );
    }

    /**
     * @throws ShareLinkNotFoundException
     * @throws DriveStructNotFoundException
     */
    public function getOne(int $structID, int $userID): DriveShareEntity
    {
        $this->assertBelongToUser($structID, $userID);

        $shareEntity = $this->shareHashesRepository->getByStructId($structID);
        if (!$shareEntity) {
            throw new ShareLinkNotFoundException('share-ссылка не найдена');
        }
        return $shareEntity;
    }

    /**
     * @throws DriveStructNotFoundException
     */
    public function delete(int $structID, int $userID): void
    {
        $this->assertBelongToUser($structID, $userID);
        $this->shareHashesRepository->deleteByStructId($structID);
    }

    /**
     * @throws DriveStructNotFoundException
     */
    private function assertBelongToUser(int $structID, int $userID): void
    {
        $noteBelongsUser = $this->driveStructRepository->isBelongToUser($structID, $userID);
        if (!$noteBelongsUser) {
            throw new DriveStructNotFoundException('Заметка не найдена');
        }
    }
}
