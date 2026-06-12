<?php

declare(strict_types=1);

namespace App\Layer\Domain\Repository;

interface ConfigRepositoryInterface
{
    public function getProjectDir(): string;

    /** @return int in bytes */
    public function getNoteFileStorageLimitPerUser(): int;

    public function getNoteFileSavePath(): string;

    public function useFileEncryption(): bool;

    public function getFileEncryptionKey(): string;

    public function getFileStorage(): string;

    public function getS3SecretAccessKey(): string;
}
