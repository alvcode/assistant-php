<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Utils;

use App\Layer\Domain\Exception\Utils\FailedDecryptionFileException;
use App\Layer\Domain\Exception\Utils\FailedEncryptionFileException;
use App\Layer\Domain\ValueObject\FileContentVO;
use Exception;
use Random\RandomException;
use SodiumException;
use SplFileInfo;

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
     * @throws SodiumException
     * @throws Exception
     */
    public function encryptFile(
        SplFileInfo $source,
        string $key
    ): SplFileInfo {
        $key = sodium_crypto_generichash(
            $key,
            '',
            SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_KEYBYTES
        );

        $destinationPath = tempnam(sys_get_temp_dir(), 'enc_');

        if ($destinationPath === false) {
            throw new Exception('Failed create temp file');
        }

        $input = fopen($source->getPathname(), 'rb');
        $output = fopen($destinationPath, 'wb');

        if ($input === false || $output === false) {
            throw new Exception('Failed open file');
        }

        try {
            [$state, $header] = sodium_crypto_secretstream_xchacha20poly1305_init_push($key);

            fwrite($output, $header);

            $chunkSize = 1024 * 1024;

            while (!feof($input)) {
                $chunk = fread($input, $chunkSize);

                if ($chunk === false) {
                    throw new Exception('Failed read file');
                }

                $isLastChunk = feof($input);

                $tag = $isLastChunk
                    ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL
                    : SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_MESSAGE;

                $encryptedChunk = sodium_crypto_secretstream_xchacha20poly1305_push(
                    $state,
                    $chunk,
                    '',
                    $tag
                );

                fwrite($output, pack('N', strlen($encryptedChunk)));
                fwrite($output, $encryptedChunk);
            }
        } finally {
            fclose($input);
            fclose($output);
        }

        return new SplFileInfo($destinationPath);
    }

    /**
     * @throws SodiumException
     * @throws Exception
     */
    public function decryptFile(
        SplFileInfo $source,
        string $key
    ): SplFileInfo {
        $key = sodium_crypto_generichash(
            $key,
            '',
            SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_KEYBYTES
        );

        $destinationPath = tempnam(sys_get_temp_dir(), 'dec_');

        if ($destinationPath === false) {
            throw new Exception('Failed create temp file');
        }

        $input = fopen($source->getPathname(), 'rb');
        $output = fopen($destinationPath, 'wb');

        if ($input === false || $output === false) {
            throw new Exception('Failed open file');
        }

        try {
            $header = fread(
                $input,
                SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES
            );

            if (
                $header === false
                || strlen($header) !== SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES
            ) {
                throw new Exception('Invalid encrypted file header');
            }

            $state = sodium_crypto_secretstream_xchacha20poly1305_init_pull(
                $header,
                $key
            );

            $finalTagReceived = false;

            while (!feof($input)) {
                $lengthBytes = fread($input, 4);

                if ($lengthBytes === '') {
                    break;
                }

                if (
                    $lengthBytes === false
                    || strlen($lengthBytes) !== 4
                ) {
                    throw new Exception('Invalid encrypted chunk length');
                }

                $length = unpack('N', $lengthBytes)[1];

                $encryptedChunk = fread($input, $length);

                if (
                    $encryptedChunk === false
                    || strlen($encryptedChunk) !== $length
                ) {
                    throw new Exception('Invalid encrypted chunk');
                }

                [$plaintext, $tag] = sodium_crypto_secretstream_xchacha20poly1305_pull(
                    $state,
                    $encryptedChunk
                );

                fwrite($output, $plaintext);

                if (
                    $tag ===
                    SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL
                ) {
                    $finalTagReceived = true;
                    break;
                }
            }

            if (!$finalTagReceived) {
                throw new Exception('Final tag not found');
            }
        } finally {
            fclose($input);
            fclose($output);
        }

        return new SplFileInfo($destinationPath);
    }
}
