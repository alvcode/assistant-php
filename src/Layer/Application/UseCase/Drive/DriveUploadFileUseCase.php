<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Drive;

use App\Layer\Application\DTO\Common\FileDTO;
use App\Layer\Application\DTO\Drive\DriveUploadFileDTO;
use App\Layer\Application\Exception\Drive\DriveFilenameExistsException;
use App\Layer\Application\Exception\Drive\DriveFilesystemIsFullException;
use App\Layer\Application\Service\TransactionManagerInterface;
use App\Layer\Domain\Dict\Common\FileSizeTypeEnum;
use App\Layer\Domain\Dict\Drive\DriveStructTypeEnum;
use App\Layer\Domain\Entity\Aggregate\DriveFileSaveAggregate;
use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Repository\DTO\Storage\SaveFileDTO;
use App\Layer\Domain\Service\Factory\Drive\DriveFileFactory;
use App\Layer\Domain\Service\Factory\Drive\DriveStructFactory;
use App\Layer\Domain\Service\Factory\Storage\StorageRepositoryFactoryInterface;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;
use App\Layer\Domain\ValueObject\FileSizeVO;

final readonly class DriveUploadFileUseCase
{
    public function __construct(
        private DriveFileRepositoryInterface $driveFileRepository,
        private ConfigRepositoryInterface $configRepository,
        private DriveStructRepositoryInterface $driveStructRepository,
        private FileUtilsInterface $fileUtils,
        private StorageRepositoryFactoryInterface $storageRepositoryFactory,
        private TransactionManagerInterface $transactionManager,
        private DriveStructFactory $driveStructFactory,
        private DriveFileFactory $driveFileFactory,
    ) {}

    public function handle(FileDTO $file, DriveUploadFileDTO $in, int $userId): DriveFileSaveAggregate
    {
        $userSpaceUsed = $this->driveFileRepository->getUsedSpaceByUserID($userId);
        $storageMaxSize = $this->configRepository->getDriveStorageLimitPerUser();

        if (($userSpaceUsed->getBytes() + $file->getFile()->getSize()) > $storageMaxSize) {
            throw new DriveFilesystemIsFullException('У пользователя нет места для загрузки файла');
        }

        $existsName = $this->driveStructRepository->findRow(
            userId: $userId,
            name: $file->getOriginalName(),
            type: DriveStructTypeEnum::File,
            parentId: $in->parentId,
        );

        if ($existsName) {
            throw new DriveFilenameExistsException('Файл с таким названием уже существует в директории');
        }

        $newFileName = $this->fileUtils->generateNewFilename($file->getOriginalExtension());
        $lastFileID = $this->driveFileRepository->getLastID();

        $middleFilePath = $this->fileUtils->pathJoin([
            $this->fileUtils->getMiddlePathByFileID($lastFileID + 1),
            $newFileName
        ]);

        $fullFilePath = $this->fileUtils->pathJoin([
            $this->configRepository->getDriveFileSavePath(),
            $middleFilePath
        ]);

        if ($this->configRepository->useFileEncryption()) {
            $fileForSave = $this->fileUtils->encryptFile(
                source: $file->getFile(),
                key: $this->configRepository->getFileEncryptionKey()
            );
        } else {
            $fileForSave = $file->getFile();
        }

        $this->storageRepositoryFactory->getRepository()->save(
            new SaveFileDTO(
                file: $fileForSave,
                savePath: $fullFilePath
            )
        );

        $driveFileSaveAggregate = $this->transactionManager->transactional(
            function() use ($file, $in, $userId, $middleFilePath): DriveFileSaveAggregate {
                $driveStructEntity = $this->driveStructRepository->save(
                    $this->driveStructFactory->getNewDriveStructFile($userId, $file->getOriginalName(), $in->parentId)
                );

                $driveFileEntity = $this->driveFileRepository->save(
                    $this->driveFileFactory->getNewDriveFile(
                        driveStructId: $driveStructEntity->getId(),
                        path: $middleFilePath,
                        ext: $file->getOriginalExtension(),
                        size: new FileSizeVO($file->getFile()->getSize(), FileSizeTypeEnum::Bytes),
                        isChunk: false, 
                        sha256: $in->sha256
                    )
                );

                return new DriveFileSaveAggregate(driveStruct: $driveStructEntity, driveFile: $driveFileEntity);
            }
        );

        return $driveFileSaveAggregate;
    }
}