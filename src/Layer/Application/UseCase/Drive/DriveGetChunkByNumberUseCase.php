<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Drive;

use App\Layer\Application\DTO\Common\FileDTO;
use App\Layer\Application\Exception\Drive\DriveFileNotFoundException;
use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Domain\Dict\Drive\DriveStructTypeEnum;
use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileChunkRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Service\Factory\Storage\StorageRepositoryFactoryInterface;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;

final readonly class DriveGetChunkByNumberUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
        private DriveFileRepositoryInterface $driveFileRepository,
        private DriveFileChunkRepositoryInterface $driveFileChunkRepository,
        private FileUtilsInterface $fileUtils,
        private ConfigRepositoryInterface $configRepository,
        private StorageRepositoryFactoryInterface $storageRepositoryFactory,
    ) {}

    /**
     * @throws DriveStructNotFoundException
     * @throws DriveFileNotFoundException
     */
    public function handle(int $structId, int $chunkNumber, int $userId): FileDTO
    {
        $driveStructEntity = $this->driveStructRepository->getById($structId);
        if (\is_null($driveStructEntity) || $driveStructEntity->getUserId() !== $userId) {
            throw new DriveStructNotFoundException('Структура не найдена');
        }
        if ($driveStructEntity->getType() !== DriveStructTypeEnum::File) {
            throw new DriveFileNotFoundException('Структура не является файлом');
        }

        $driveFileEntity = $this->driveFileRepository->getByStructId($driveStructEntity->getId());
        if (\is_null($driveFileEntity) || !$driveFileEntity->isChunk()) {
            throw new DriveFileNotFoundException('Файл не найден');
        }

        $driveFileChunkEntity = $this->driveFileChunkRepository->getByFileIDAndNumber(
            $driveFileEntity->getId(), 
            $chunkNumber
        );

        if (!$driveFileChunkEntity) {
            throw new DriveFileNotFoundException('Чанк не найден');
        }

        $fullFilePath = $this->fileUtils->pathJoin([
            $this->configRepository->getDriveFileSavePath(),
            $driveFileChunkEntity->getPath()
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