<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Drive;

use App\Layer\Application\DTO\Drive\DriveCreateDirectoryDTO;
use App\Layer\Application\Exception\Drive\DriveDirectoryExistsException;
use App\Layer\Domain\Dict\Drive\DriveStructTypeEnum;
use App\Layer\Domain\Entity\DriveStructEntity;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Service\Factory\Drive\DriveStructFactory;

final readonly class DriveCreateDirectoryUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
        private DriveStructFactory $driveStructFactory,
    ) {}

    /**
     * @throws DriveDirectoryExistsException
     */
    public function handle(DriveCreateDirectoryDTO $in, int $userId): DriveStructEntity
    {
        $existsDirectory = $this->driveStructRepository->findRow(
            userId: $userId,
            name: $in->name,
            type: DriveStructTypeEnum::Directory,
            parentId: $in->parentId,
        );
        if ($existsDirectory) {
            throw new DriveDirectoryExistsException('Директория уже существует');
        }

        return $this->driveStructRepository->save(
            $this->driveStructFactory->getNewDriveStructDirectory(
                userId: $userId,
                name: $in->name,
                parentId: $in->parentId
            )
        );
    }
}
