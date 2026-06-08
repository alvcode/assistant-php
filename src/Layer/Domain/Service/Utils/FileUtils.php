<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Utils;

use App\Layer\Domain\Exception\Utils\FailedDecryptionFileException;
use App\Layer\Domain\Exception\Utils\FailedEncryptionFileException;
use App\Layer\Domain\ValueObject\FileContentVO;
use Random\RandomException;

final readonly class FileUtils
{
    public function __construct(
        private HasherServiceInterface $hasherService,
    ) {}

    public function generateNewFilename(string $extension): string
    {
        return sprintf(
            "%d_%s.%s",
            time(),
            $this->hasherService->generateRandomStringWithoutSymbols(10),
            $extension
        );
    }

    public function getMiddlePathByFileID(int $fileID): string
    {
        $dirLevel1 = $fileID / 1_000_000;
        $dirLevel2 = ($fileID % 1_000_000) / 1_000;
        return sprintf("%d/%d/", $dirLevel1+1, $dirLevel2+1);
    }

    /** @param string[] $parts */
    public function pathJoin(array $parts, bool $isAbsolute = false): string
    {
        $parts = array_map(
            static fn(string $part): string => trim($part, '/\\'),
            $parts
        );
        $res = implode(DIRECTORY_SEPARATOR, $parts);
        if ($isAbsolute) {
            $res = '/' . $res;
        }
        return $res;
    }

    /**
     * @throws RandomException
     * @throws FailedEncryptionFileException
     */
    public function encryptFile(string $content, string $key): FileContentVO
    {
        $key = hash('sha256', $key, true);
        $nonce = random_bytes(12);

        $ciphertext = openssl_encrypt(
            $content,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag
        );

        if ($ciphertext === false) {
            throw new FailedEncryptionFileException('Ошибка шифрования файла');
        }

        return new FileContentVO($nonce . $tag . $ciphertext);
    }

    /**
     * @throws FailedDecryptionFileException
     */
    function decryptFile(string $content, string $key): FileContentVO
    {
        $key = hash('sha256', $key, true);

        $nonce = substr($content, 0, 12);
        $tag = substr($content, 12, 16);
        $ciphertext = substr($content, 28);

        $plaintext = openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag
        );

        if ($plaintext === false) {
            throw new FailedDecryptionFileException('Ошибка дешифровки файла');
        }

        return new FileContentVO($plaintext);
    }
}
