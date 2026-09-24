<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Drive;

use App\Layer\Domain\Dict\Common\FileSizeTypeEnum;
use App\Layer\Domain\Exception\Common\FileFopenException;
use App\Layer\Domain\Exception\Common\FileFreadException;
use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\DTO\Storage\SaveFileDTO;
use App\Layer\Domain\Service\Factory\Storage\StorageRepositoryFactoryInterface;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;
use App\Layer\Domain\ValueObject\Drive\DriveFileChunkVO;
use App\Layer\Domain\ValueObject\FileSizeVO;
use App\Layer\Domain\ValueObject\PathVO;
use Generator;
use SplFileInfo;

/**
 * Разбивает файл на чанки, сохраняем внутрь переданного $savePath, возвращает чанки через yield
 */
final readonly class SplitFileIntoChunksService
{
    private const int ARCHIVE_CHUNK_SIZE_BYTES = 50 * 1024 * 1024; // 50 mb
    private const int STREAM_CHUNK_SIZE = 1024 * 1024;

    public function __construct(
        private FileUtilsInterface $fileUtils,
        private StorageRepositoryFactoryInterface $storageRepositoryFactory,
        private ConfigRepositoryInterface $configRepository,
    ) {}

    /**
     * @return Generator<DriveFileChunkVO>
     * @throws FileFopenException
     * @throws FileFreadException
     */
    public function handle(PathVO $filePath, PathVO $savePath): Generator
    {
        $filePath = $filePath->isAbsolute() ? $filePath->getPath() : $this->fileUtils->pathJoin(
            [$this->configRepository->getProjectDir(), $filePath->getPath()],
            true
        );
        $input = fopen($filePath, 'rb');
        if ($input === false) {
            throw new FileFopenException('Не удалось открыть архив для чтения');
        }

        $chunkNumber = 1;

        try {
            while (!feof($input)) {
                $chunkTempFile = $this->fileUtils->createTempFile();
                $output = fopen($chunkTempFile->getFile()->getPathname(), 'wb');
                if ($output === false) {
                    throw new FileFopenException('Не удалось создать чанк архива');
                }

                $chunkSize = 0;
                try {
                    while ($chunkSize < self::ARCHIVE_CHUNK_SIZE_BYTES && !feof($input)) {
                        $data = fread($input, self::STREAM_CHUNK_SIZE);
                        if ($data === false) {
                            throw new FileFreadException('Не удалось прочитать архив');
                        }
                        if ($data === '') {
                            break;
                        }
                        fwrite($output, $data);
                        $chunkSize += strlen($data);
                    }
                } finally {
                    fclose($output);
                }

                if ($chunkSize === 0) {
                    $chunkTempFile->unlinkIfTemporary();
                    break;
                }

                $chunkSavePath = $this->fileUtils->pathJoin([
                    $savePath->getPath(),
                    $this->fileUtils->generateNewFilename(sprintf('archive_part_%d', $chunkNumber)),
                ]);

                $this->storageRepositoryFactory->getLocalStorage()->save(
                    new SaveFileDTO(
                        file: $chunkTempFile->getFile(),
                        savePath: new PathVO($chunkSavePath),
                    )
                );
                $chunkTempFile->unlinkIfTemporary();

                $result = new DriveFileChunkVO(
                    path: new PathVO($chunkSavePath),
                    size: new FileSizeVO(size: $chunkSize, sizeType: FileSizeTypeEnum::Bytes),
                    chunkNumber: $chunkNumber,
                );

                $chunkNumber++;

                yield $result;
            }
        } finally {
            fclose($input);
        }
    }
}
