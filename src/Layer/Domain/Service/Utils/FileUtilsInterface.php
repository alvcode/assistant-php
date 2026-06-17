<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Utils;

use App\Layer\Domain\Exception\Utils\FailedCreateTempFileException;
use App\Layer\Domain\Exception\Utils\FailedDecryptionFileException;
use App\Layer\Domain\Exception\Utils\FailedEncryptionFileException;
use SplFileInfo;

interface FileUtilsInterface
{
    public function generateNewFilename(string $extension): string;

    public function getMiddlePathByFileID(int $fileID): string;

    /** @param string[] $parts */
    public function pathJoin(array $parts, bool $isAbsolute = false): string;

    function getExtensionByName(string $filename): string;

    /** @throws FailedEncryptionFileException */
    public function encryptFile(SplFileInfo $source, string $key): SplFileInfo;

    /** @throws FailedDecryptionFileException */
    public function decryptFile(SplFileInfo $source, string $key): SplFileInfo;

    /** @throws FailedCreateTempFileException */
    public function createTempFile(): string;
}
