<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Drive;

use App\Layer\Application\DTO\Drive\DriveChunkPrepareDTO;
use App\Layer\Application\Exception\Drive\DriveFilenameExistsException;
use App\Layer\Application\Exception\Drive\DriveFilesystemIsFullException;
use App\Layer\Application\Exception\Drive\DriveFileTooLargeException;
use App\Layer\Application\Exception\Drive\DriveNotSafeFilenameException;
use App\Layer\Application\Exception\Drive\DriveParentIdNotFoundException;
use App\Layer\Application\Service\TransactionManagerInterface;
use App\Layer\Domain\Dict\Drive\DriveStructTypeEnum;
use App\Layer\Domain\Entity\DriveStructEntity;
use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Service\Factory\Drive\DriveFileFactory;
use App\Layer\Domain\Service\Factory\Drive\DriveStructFactory;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;

final readonly class DriveChunkPrepareUseCase
{
    public function __construct(
        private ConfigRepositoryInterface $configRepository,
        private DriveFileRepositoryInterface $driveFileRepository,
        private DriveStructRepositoryInterface $driveStructRepository,
        private DriveStructFactory $driveStructFactory,
        private DriveFileFactory $driveFileFactory,
        private TransactionManagerInterface $transactionManager,
        private FileUtilsInterface $fileUtils,
    ) {}

    /**
     * @return integer structId
     * @throws DriveFileTooLargeException
     * @throws DriveFilesystemIsFullException
     * @throws DriveParentIdNotFoundException
     * @throws DriveNotSafeFilenameException
     * @throws DriveFilenameExistsException
     */
    public function handle(DriveChunkPrepareDTO $in, int $userId): int
    {
        if ($in->size->getBytes() > $this->configRepository->getDriveUploadMaxSize()) {
            throw new DriveFileTooLargeException('Файл слишком большой');
        }

        $userUsedSpace = $this->driveFileRepository->getUsedSpaceByUserID($userId);
        if (($userUsedSpace->getBytes() + $in->size->getBytes()) > $this->configRepository->getDriveStorageLimitPerUser()) {
            throw new DriveFilesystemIsFullException('Диск переполнен');
        }

        if (!\is_null($in->parentId)) {
            $parentDriveStructEntity = $this->driveStructRepository->getById($in->parentId);
            if (!$parentDriveStructEntity || $parentDriveStructEntity->getUserId() !== $userId) {
                throw new DriveParentIdNotFoundException('Родительская структура не найдена');
            }
        }

         if (
            str_contains($in->fileName, '..')
            || str_contains($in->fileName, '/')
            || str_contains($in->fileName, '\\')
        ) {
            throw new DriveNotSafeFilenameException('Небезопасное имя файла');
        }

        $foundDuplicateStruct = $this->driveStructRepository->findRow(
            userId: $userId, 
            name: $in->fileName, 
            type: DriveStructTypeEnum::File, 
            parentId: $in->parentId
        );
        if (!\is_null($foundDuplicateStruct)) {
            throw new DriveFilenameExistsException('Файл с таким именем уже существует');
        }

        /** @var DriveStructEntity $driveStructEntity */
        $driveStructEntity = $this->transactionManager->transactional(
            function() use ($userId, $in): DriveStructEntity {
                $driveStructEntity = $this->driveStructRepository->save(
                    $this->driveStructFactory->getNewDriveStructFile($userId, $in->fileName, $in->parentId)
                );

                $this->driveFileRepository->save(
                    $this->driveFileFactory->getForPrepareChunk(
                        $driveStructEntity->getId(),
                        $this->fileUtils->getExtensionByName($in->fileName),
                        $in->sha256
                    )
                );

                return $driveStructEntity;
            }
        );

        return $driveStructEntity->getId();
    }
}