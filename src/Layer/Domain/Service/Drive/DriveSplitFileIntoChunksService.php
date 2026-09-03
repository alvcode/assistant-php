<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Drive;

use App\Layer\Domain\Exception\Common\FileFopenException;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;
use App\Layer\Domain\ValueObject\FileSizeVO;
use Generator;

final readonly class DriveSplitFileIntoChunksService
{
    public function __construct(
        private FileUtilsInterface $fileUtils,
    ) {}

    /**
     * @return Generator<FileSizeVO>
     * @throws FileFopenException
     */
    public function service(string $filePath, FileSizeVO $chunkSize): Generator
    {
        $input = fopen($filePath, 'rb');
        if ($input === false) {
            throw new FileFopenException('Не удалось открыть архив для чтения');
        }

        $chunkNumber = 0;
        $size = 0;

        try {
            while (!feof($input)) {
                $chunkTempPath = $this->fileUtils->createTempFile();
                $output = fopen($chunkTempPath, 'wb');
                if ($output === false) {
                    throw new Exception('Не удалось создать чанк архива');
                }

                $size = 0;
                try {
                    while ($chunkSize < $chunkSize->getBytes() && !feof($input)) {
                        $data = fread($input, 1024 * 1024);
                        if ($data === false) {
                            throw new Exception('Не удалось прочитать архив');
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
                    unlink($chunkTempPath);
                    break;
                }

                $chunkSavePath = $this->fileUtils->pathJoin([
                    $this->configRepository->getTempSavePath(),
                    'archives',
                    $driveArchiveFileId . '_chunks',
                    $this->fileUtils->generateNewFilename(sprintf('archive_part_%d', $chunkNumber)),
                ]);

                $this->storageRepositoryFactory->getLocalStorage()->save(
                    new SaveFileDTO(
                        file: new SplFileInfo($chunkTempPath),
                        savePath: $chunkSavePath,
                    )
                );
                unlink($chunkTempPath);

                $this->driveArchiveFileChunkRepository->save(
                    new DriveArchiveFileChunkEntity(
                        id: null,
                        driveArchiveFileId: $driveArchiveFileId,
                        path: $chunkSavePath,
                        size: new FileSizeVO(size: $chunkSize, sizeType: FileSizeTypeEnum::Bytes),
                        chunkNumber: $chunkNumber,
                    )
                );

                $totalSize += $chunkSize;
                $chunkNumber++;
            }
        } finally {
            fclose($input);
        }
    }
}
