<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveRecycleBin;

use App\Layer\Application\Exception\DriveRecycleBin\DriveRecycleBinNotFoundException;
use App\Layer\Domain\Dict\Drive\DriveStructTypeEnum;
use App\Layer\Domain\Entity\DriveStructEntity;
use App\Layer\Domain\Repository\DriveRecycleBinRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Service\Factory\Drive\DriveStructFactory;
use App\Layer\Domain\Service\Utils\HasherServiceInterface;

final readonly class DriveRBRestoreOneUseCase
{
    public function __construct(
        private DriveRecycleBinRepositoryInterface $driveRecycleBinRepository,
        private DriveStructRepositoryInterface $driveStructRepository,
        private HasherServiceInterface $hasherService,
        private DriveStructFactory $driveStructFactory,
    ) {}

    /**
     * @throws DriveRecycleBinNotFoundException
     */
    public function handle(int $id, int $userId): void
    {
        $driveRecycleBinEntity = $this->driveRecycleBinRepository->getById($id);
        if (is_null($driveRecycleBinEntity)) {
            throw new DriveRecycleBinNotFoundException('Запись корзины не найдена');
        }

        $nestedDriveStructs = $this->driveStructRepository->getAllRecursiveBackward(
            $driveRecycleBinEntity->getDriveStructId(),
            $userId
        );
        /** @var DriveStructEntity[] $nestedDriveStructs */
        $nestedDriveStructs = array_reverse($nestedDriveStructs);
        $recycleBinPathArray = $driveRecycleBinEntity->getOriginalPath()->getAsArray();

        $structPathMatrix = [];
        foreach ($recycleBinPathArray as $key => $recycleBinPath) {
            $struct = isset($nestedDriveStructs[$key]) && $nestedDriveStructs[$key]->getName() === $recycleBinPath
                ? $nestedDriveStructs[$key] : null;

            $structPathMatrix[] = [
                'name' => $recycleBinPath,
                'isExists' => !is_null($struct),
                'struct' => $struct
            ];
        }

        $structPathMatrix[] = [
            'name' => $nestedDriveStructs[count($nestedDriveStructs) - 1]->getName(),
            'isExists' => true,
            'struct' => $nestedDriveStructs[count($nestedDriveStructs) - 1]
        ];

        $lastParentId = null;
        foreach ($structPathMatrix as $structPathMatrixItem) {
            if ($structPathMatrixItem['isExists']) {
                /** @var DriveStructEntity $structEntity */
                $structEntity = $structPathMatrixItem['struct'];
                if ($structEntity->getParentId() !== $lastParentId) {
                    $structEntity->setParentId($lastParentId);
                }
                if ($this->isExistsStructName(
                    $userId,
                    $structEntity->getName(),
                    $lastParentId,
                    $structEntity->getId()
                )) {
                    $structEntity->generateRestoredName($this->hasherService);
                }
            } else {
                // будем использовать существующую папку, если уже есть
                $foundStructEntity = $this->driveStructRepository->findRow(
                    userId: $userId,
                    name: $structPathMatrixItem['name'],
                    type: DriveStructTypeEnum::Directory,
                    includeRecycleBin: false,
                    parentId: $lastParentId
                );

                if (!is_null($foundStructEntity)) {
                    $lastParentId = $foundStructEntity->getId();
                    continue;
                }

                // папку не нашли, создадим её
                $structEntity = $this->driveStructFactory->getNewDriveStructDirectory(
                    userId: $userId,
                    name: $structPathMatrixItem['name'],
                    parentId: $lastParentId
                );

                if ($this->isExistsStructName(
                    $userId,
                    $structPathMatrixItem['name'],
                    $lastParentId,
                    null
                )) {
                    $structEntity->generateRestoredName($this->hasherService);
                }
            }
            $structEntity = $this->driveStructRepository->save($structEntity);
            $lastParentId = $structEntity->getId();
        }

        $this->driveRecycleBinRepository->delete($driveRecycleBinEntity);
    }

    private function isExistsStructName(int $userId, string $structName, ?int $parentId, ?int $excludeStructId): bool
    {
        return $this->driveStructRepository->checkExistsByName($userId, $structName, $parentId, $excludeStructId);
    }
}
