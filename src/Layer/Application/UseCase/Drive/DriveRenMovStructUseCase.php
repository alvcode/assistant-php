<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Drive;

use App\Layer\Application\DTO\Drive\DriveRenMovDTO;
use App\Layer\Application\Exception\Drive\DriveParentIdNotFoundException;
use App\Layer\Application\Exception\Drive\DriveParentRefOfTheRelocatableStructException;
use App\Layer\Application\Exception\Drive\DriveRelocatableStructureNotFoundException;
use App\Layer\Domain\Dict\Drive\DriveStructTypeEnum;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;

final readonly class DriveRenMovStructUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
    ) {}

    /** 
     * @throws DriveParentIdNotFoundException 
     * @throws DriveParentRefOfTheRelocatableStructException 
     * @throws DriveRelocatableStructureNotFoundException
     * */
    public function handle(DriveRenMovDTO $in, int $userId): void
    {
        if (!\is_null($in->parentId)) {
            $parentStruct = $this->driveStructRepository->getById($in->parentId);

            if (
                \is_null($parentStruct) 
                || $parentStruct->getUserId() !== $userId
                || $parentStruct->getType() !== DriveStructTypeEnum::Directory
            ) {
                throw new DriveParentIdNotFoundException('Родительская структура не найдена');
            }

            if (
                !\is_null($parentStruct->getParentId())
                && \in_array($parentStruct->getParentId(), $in->structIds, true)
            ) {
                throw new DriveParentRefOfTheRelocatableStructException(
                    'Родительская структура ссылается на одну из перемещаемых структур'
                );
            }
        }

        $structCount = $this->driveStructRepository->structCountByUserAndIds($userId, $in->structIds);
        if ($structCount !== count($in->structIds)) {
            throw new DriveRelocatableStructureNotFoundException('Перемещаемая структура не найдена');
        }

        $this->driveStructRepository->massUpdateParentId($in->parentId, $in->structIds);
    }
}