<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Service\Factory\Storage;

use App\Layer\Domain\Exception\Storage\FailedStorageConfigurationException;
use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\StorageRepositoryInterface;
use App\Layer\Domain\Service\Factory\Storage\StorageRepositoryFactoryInterface;
use App\Layer\Infrastructure\Repository\LocalStorageRepository;
use App\Layer\Infrastructure\Repository\S3StorageRepository;

final readonly class StorageRepositoryFactory implements StorageRepositoryFactoryInterface
{
    public function __construct(
        private ConfigRepositoryInterface $configRepository,
        private LocalStorageRepository    $localStorageRepository,
        private S3StorageRepository       $s3StorageRepository,
    )
    {}

    /** @inheritDoc */
    public function getRepository(): StorageRepositoryInterface
    {
        $driver = $this->configRepository->getFileStorage();

        if ($driver === 'local') {
            return $this->localStorageRepository;
        }

        if ($driver === 's3' && !empty($this->configRepository->getS3SecretAccessKey())) {
            return $this->s3StorageRepository;
        }

        throw new FailedStorageConfigurationException('Не удалось определить storage');
    }

    public function getLocalStorage(): StorageRepositoryInterface
    {
        return $this->localStorageRepository;
    }

    public function getS3Storage(): StorageRepositoryInterface
    {
        return $this->s3StorageRepository;
    }
}
