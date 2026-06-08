<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final readonly class ConfigRepository implements ConfigRepositoryInterface
{
    public function __construct(
        private ParameterBagInterface $parameterBag,
    ) {}

    public function getProjectDir(): string
    {
        return $this->parameterBag->get('kernel.project_dir');
    }

    /** @inheritDoc */
    public function getNoteFileStorageLimitPerUser(): int
    {
        $config = $this->parameterBag->get('noteFile.limitStoragePerUser');
        return $config * 1000000;
    }

    public function getNoteFileSavePath(): string
    {
        return $this->parameterBag->get('noteFile.savePath');
    }

    public function useFileEncryption(): bool
    {
        return $this->parameterBag->get('file.useFileEncryption') === 'true';
    }

    public function getFileEncryptionKey(): string
    {
        return $this->parameterBag->get('file.encryptionKey');
    }
}
