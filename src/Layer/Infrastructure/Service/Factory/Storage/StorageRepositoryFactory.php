<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Service\Factory\Storage;

use App\Layer\Domain\Exception\Storage\FailedStorageConfigurationException;
use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\StorageRepositoryInterface;
use App\Layer\Domain\Service\Factory\Storage\StorageRepositoryFactoryInterface;
use App\Layer\Infrastructure\Repository\LocalStorageRepository;
use App\Layer\Infrastructure\Repository\S3StorageRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

final readonly class StorageRepositoryFactory implements StorageRepositoryFactoryInterface
{
    public function __construct(
        private ConfigRepositoryInterface $configRepository,
        private ContainerInterface $container,
        //private LocalStorageRepository $localStorageRepository,
        //private S3StorageRepository $s3StorageRepository,
    ) {}

    /** @inheritDoc */
    public function getRepository(): StorageRepositoryInterface
    {
        $driver = $this->configRepository->getFileStorage();

        if ($driver === 'local') {
            return $this->container->get(LocalStorageRepository::class);
            //return $this->localStorageRepository;
        }

        if ($driver === 's3' && !empty($this->configRepository->getS3SecretAccessKey())) {
            return $this->container->get(S3StorageRepository::class);
            //return $this->s3StorageRepository;
        }

        throw new FailedStorageConfigurationException('Не удалось определить storage');
    }
}
