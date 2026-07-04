<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveRecycleBin;

use App\Layer\Application\Exception\DriveRecycleBin\DriveRecycleBinNotFoundException;
use App\Layer\Domain\Entity\DriveStructEntity;
use App\Layer\Domain\Repository\DriveRecycleBinRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;

final readonly class DriveRBRestoreOneUseCase
{
    public function __construct(
        private DriveRecycleBinRepositoryInterface $driveRecycleBinRepository,
        private DriveStructRepositoryInterface $driveStructRepository,
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

        /**
         * когда восстанавливаем...идем рекурсивно. если папка существует, то переименовываем добавляя restored_HaSh
         *  если файл существует, то переименовываем добавляя restored_HaSh.
         *  в конце просто удаляем запись корзины и вся цепочка становится доступной.
         *  -- если папки не существует, то создаем её
         */

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

        //dd($structPathMatrix);

        $log = [];

        $lastParentId = null;
        foreach ($structPathMatrix as $structPathMatrixItem) {
            $log[] = '====== Обработка name: ' . $structPathMatrixItem['name'];

            if ($structPathMatrixItem['isExists']) {
                if ($structPathMatrixItem['struct']->getParentId() !== $lastParentId) {
                    $log[] = 'меняем у структуры parentId на ' . $lastParentId;
                }
                if ($this->isExistsStructName(
                    $userId,
                    $structPathMatrixItem['struct']->getName(),
                    $lastParentId,
                    $structPathMatrixItem['struct']->getId()
                )) {
                    $log[] = 'Меняем имя структуры';
                }
                $lastParentId = rand(1, 100); // проставляем сюда id существующей структуры
            } else {
                $log[] = 'Создаем папку: ' . $structPathMatrixItem['name'];
                if (!is_null($lastParentId)) {
                    $log[] = 'у данной папки проставляем parentId: ' . $lastParentId;
                }
                if ($this->isExistsStructName(
                    $userId,
                    $structPathMatrixItem['name'],
                    $lastParentId,
                    null
                )) {
                    $log[] = 'Меняем имя структуры';
                }
                $lastParentId = rand(1, 100); // проставляем сюда id созданной структуры
            }
        }

        dd($log);

        foreach ($nestedDriveStructs as $key => $nestedDriveStruct) {

        }

        foreach ($nestedDriveStructs as $key => $nestedDriveStruct) {
            if ($nestedDriveStruct->getId() === $driveRecycleBinEntity->getDriveStructId()) {

            }

            // при восстановлении папки мы должны перезаписывать parent_id у следующей структуры
        }

        dd($nestedDriveStructs);
    }

    private function isExistsStructName(int $userId, string $structName, ?int $parentId, ?int $excludeStructId): bool
    {
        return $this->driveStructRepository->checkExistsByName($userId, $structName, $parentId, $excludeStructId);
    }
}
