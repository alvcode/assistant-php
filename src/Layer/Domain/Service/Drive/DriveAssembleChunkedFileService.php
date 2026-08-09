<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Drive;

use App\Layer\Domain\Dict\Drive\DriveStructTypeEnum;
use App\Layer\Domain\Exception\Drive\DriveFileNotFoundException;
use App\Layer\Domain\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileChunkRepositoryInterface;
use App\Layer\Domain\Repository\DriveFileRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Service\Factory\Storage\StorageRepositoryFactoryInterface;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;
use Exception;
use SplFileInfo;

/**
 * Собирает все чанки файла в один временный файл, расшифровывая их при необходимости.
 */
final readonly class DriveAssembleChunkedFileService
{
    private const STREAM_CHUNK_SIZE = 1024 * 1024;

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
     * @throws Exception
     */
    public function handle(int $structId, int $userId): SplFileInfo
    {
        $driveStructEntity = $this->driveStructRepository->getById($structId, false);
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

        $chunks = $this->driveFileChunkRepository->getAllByFileId($driveFileEntity->getId());
        usort(
            $chunks,
            static fn($left, $right): int => $left->getChunkNumber() <=> $right->getChunkNumber()
        );

        $tempPath = $this->fileUtils->createTempFile();
        $output = fopen($tempPath, 'wb');
        if ($output === false) {
            throw new Exception('Ошибка открытия временного файла');
        }

        try {
            foreach ($chunks as $chunk) {
                $fullFilePath = $this->fileUtils->pathJoin([
                    $this->configRepository->getDriveFileSavePath(),
                    $chunk->getPath()
                ]);

                $chunkFile = $this->storageRepositoryFactory->getRepository()->getFile($fullFilePath);

                if ($this->configRepository->useFileEncryption()) {
                    $chunkFile = $this->fileUtils->decryptFile(
                        source: $chunkFile,
                        key: $this->configRepository->getFileEncryptionKey()
                    );
                }

                $this->appendFile($chunkFile, $output);
            }
        } finally {
            fclose($output);
        }

        return new SplFileInfo($tempPath);
    }

    /** @param resource $output
     * @throws Exception
     */
    private function appendFile(SplFileInfo $file, $output): void
    {
        $input = fopen($file->getPathname(), 'rb');
        if ($input === false) {
            throw new Exception('Ошибка открытия файла');
        }

        try {
            while (!feof($input)) {
                $data = fread($input, self::STREAM_CHUNK_SIZE);
                if ($data === false) {
                    throw new Exception('Ошибка чтения файла');
                }
                fwrite($output, $data);
            }
        } finally {
            fclose($input);
        }
    }
}
