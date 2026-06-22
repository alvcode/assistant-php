<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Drive;

use App\Layer\Application\DTO\Common\FileDTO;
use App\Layer\Application\Exception\Drive\DriveFileNotFoundException;
use App\Layer\Application\Exception\Drive\DriveUnavailableForChunkException;
use App\Layer\Domain\Dict\Drive\DriveStructTypeEnum;
use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Service\Factory\Storage\StorageRepositoryFactoryInterface;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;

final readonly class DriveGetFileUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
        private DriveFileRepositoryInterface $driveFileRepository,
        private FileUtilsInterface $fileUtils,
        private StorageRepositoryFactoryInterface $storageRepositoryFactory,
        private ConfigRepositoryInterface $configRepository,
    ) {}

    /**
     * @throws DriveFileNotFoundException
     * @throws DriveUnavailableForChunkException
     */
    public function handle(int $structId, int $userId): FileDTO
    {
        $driveStructEntity = $this->driveStructRepository->getById($structId);
        if (
            \is_null($driveStructEntity)
            || $driveStructEntity->getUserId() !== $userId
            || $driveStructEntity->getType() !== DriveStructTypeEnum::File
        ) {
            throw new DriveFileNotFoundException('Структура не найдена');
        }

        $driveFileEntity = $this->driveFileRepository->getByStructId($driveStructEntity->getId());
        if (\is_null($driveFileEntity)) {
            throw new DriveFileNotFoundException('Файл не найден');
        }

        if ($driveFileEntity->isChunk()) {
            throw new DriveUnavailableForChunkException('Попытка получения чанка методом получения файла');
        }

        $fullFilePath = $this->fileUtils->pathJoin([
            $this->configRepository->getDriveFileSavePath(),
            $driveFileEntity->getPath()
        ]);

        if ($this->configRepository->useFileEncryption()) {
            $file = $this->fileUtils->decryptFile(
                source: $this->storageRepositoryFactory->getRepository()->getFile($fullFilePath),
                key: $this->configRepository->getFileEncryptionKey()
            );
        } else {
            $file = $this->storageRepositoryFactory->getRepository()->getFile($fullFilePath);
        }

        return new FileDTO(
            file: $file,
            originalExtension: $driveFileEntity->getExt(),
            originalName: $driveStructEntity->getName()
        );
    }
}
