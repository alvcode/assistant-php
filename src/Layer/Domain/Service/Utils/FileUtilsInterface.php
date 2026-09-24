<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Utils;

use App\Layer\Domain\Exception\Utils\FailedCreateTempFileException;
use App\Layer\Domain\Exception\Utils\FailedDecryptionFileException;
use App\Layer\Domain\Exception\Utils\FailedEncryptionFileException;
use App\Layer\Domain\ValueObject\PathVO;
use App\Layer\Domain\ValueObject\SplFileInfoVO;
use SplFileInfo;

interface FileUtilsInterface
{
    public function generateNewFilename(string $extension): string;

    public function getMiddlePathByFileID(int $fileID): string;

    /** @param array<int, int|string|null> $parts */
    public function pathJoin(array $parts, bool $isAbsolute = false): string;

    function getExtensionByName(string $filename): string;

    /** @throws FailedEncryptionFileException */
    public function encryptFile(SplFileInfo $source, string $key): SplFileInfoVO;

    /** @throws FailedDecryptionFileException */
    public function decryptFile(SplFileInfo $source, string $key): SplFileInfoVO;

    /** @throws FailedCreateTempFileException */
    public function createTempFile(): SplFileInfoVO;

    public function createArchive(PathVO $sourcePath, PathVO $destinationPath): void;

    public function unlinkPath(PathVO $path): void;

    public function isPathExists(PathVO $path): bool;
}
