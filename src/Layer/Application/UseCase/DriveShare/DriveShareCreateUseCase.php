<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveShare;

use App\Layer\Application\Exception\Common\FailedToGenerateUniqueHashException;
use App\Layer\Application\Exception\Drive\DriveShareExistsException;
use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Domain\Dict\Common\ShareDict;
use App\Layer\Domain\Entity\DriveShareEntity;
use App\Layer\Domain\Repository\DriveShareHashesRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Service\Utils\HasherServiceInterface;

final readonly class DriveShareCreateUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
        private DriveShareHashesRepositoryInterface $shareHashesRepository,
        private HasherServiceInterface $hasherService,
    ) {}

    /**
     * @throws DriveStructNotFoundException
     * @throws FailedToGenerateUniqueHashException
     * @throws DriveShareExistsException
     */
    public function handle(int $structID, int $userID): DriveShareEntity
    {
        $noteBelongsUser = $this->driveStructRepository->isBelongToUser($structID, $userID);
        if (!$noteBelongsUser) {
            throw new DriveStructNotFoundException('Заметка не найдена');
        }

        $existsShare = $this->shareHashesRepository->existsByStructId($structID);
        if ($existsShare) {
            throw new DriveShareExistsException('Структурой уже поделились');
        }

        $hash = null;
        for ($i = 0; $i < 20; ++$i) {
            $h = $this->hasherService->generateRandomStringWithoutSymbols(ShareDict::HASH_LENGTH);
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
}
