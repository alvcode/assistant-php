<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\Drive;

use App\Layer\Application\DTO\Common\FileDTO;
use App\Layer\Application\DTO\Drive\DriveUploadChunkDTO;
use App\Layer\Application\Exception\Drive\DriveFileTooLargeException;
use App\Layer\Application\Exception\Drive\DriveStructIsNotChunkException;
use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Domain\Dict\Common\FileSizeTypeEnum;
use App\Layer\Domain\Entity\DriveFileChunkEntity;
use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileChunkRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Repository\DTO\Storage\SaveFileDTO;
use App\Layer\Domain\Service\Factory\Storage\StorageRepositoryFactoryInterface;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;
use App\Layer\Domain\ValueObject\FileSizeVO;

final readonly class DriveChunkUploadUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
        private DriveFileRepositoryInterface $driveFileRepository,
        private ConfigRepositoryInterface $configRepository,
        private FileUtilsInterface $fileUtils,
        private DriveFileChunkRepositoryInterface $driveFileChunkRepository,
        private StorageRepositoryFactoryInterface $storageRepositoryFactory,
    ) {}

    /**
     * @throws DriveStructNotFoundException
     * @throws DriveFileTooLargeException
     * @throws DriveStructIsNotChunkException
    */
    public function handle(FileDTO $file, DriveUploadChunkDTO $in, int $userId): void
    {
        $driveStructEntity = $this->driveStructRepository->getById($in->structId, false);
        if (!$driveStructEntity || $driveStructEntity->getUserId() !== $userId) {
            throw new DriveStructNotFoundException('Структура не найдена');
        }

        $driveFileEntity = $this->driveFileRepository->getByStructId($driveStructEntity->getId());
        if (!$driveFileEntity) {
            throw new DriveStructNotFoundException('Структура не найдена');
        }
        if (!$driveFileEntity->isChunk()) {
            throw new DriveStructIsNotChunkException('Файл не является загруженным с помощью чанков');
        }

        if ($this->configRepository->useFileEncryption()) {
            $fileForSave = $this->fileUtils->encryptFile(
                source: $file->getFile(),
                key: $this->configRepository->getFileEncryptionKey()
            );
        } else {
            $fileForSave = $file->getFile();
        }

        $uploadedChunkSize = $this->driveFileChunkRepository->getChunksSize($driveFileEntity->getId());
        $uploadMaxSize = $this->configRepository->getDriveUploadMaxSize();
        if (($uploadedChunkSize->getBytes() + $file->getFile()->getSize()) > $uploadMaxSize) {
            throw new DriveFileTooLargeException('Файл слишком большой');
        }

        $newFilename = $this->fileUtils->generateNewFilename(\sprintf("%s_%d", "part", $in->chunkNumber));

        $middleFilePath = $this->fileUtils->pathJoin([
            $this->fileUtils->getMiddlePathByFileID($driveFileEntity->getId()),
            $newFilename
        ]);

        $fullFilePath = $this->fileUtils->pathJoin([
            $this->configRepository->getDriveFileSavePath(),
            $middleFilePath
        ]);

        $this->storageRepositoryFactory->getRepository()->save(
            new SaveFileDTO(
                file: $fileForSave,
                savePath: $fullFilePath
            )
        );

        $this->driveFileChunkRepository->save(
            new DriveFileChunkEntity(
                id: null,
                driveFileId: $driveFileEntity->getId(),
                path: $middleFilePath,
                size: new FileSizeVO(size: $file->getFile()->getSize(), sizeType: FileSizeTypeEnum::Bytes),
                chunkNumber: $in->chunkNumber
            )
        );
    }
}
