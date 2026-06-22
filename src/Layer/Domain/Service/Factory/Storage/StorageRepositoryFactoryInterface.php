<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Factory\Storage;

use App\Layer\Domain\Exception\Storage\FailedStorageConfigurationException;
use App\Layer\Domain\Repository\StorageRepositoryInterface;

interface StorageRepositoryFactoryInterface
{
    /**
     * @throws FailedStorageConfigurationException
     */
    public function getRepository(): StorageRepositoryInterface;

    public function getLocalStorage(): StorageRepositoryInterface;

    public function getS3Storage(): StorageRepositoryInterface;
}
