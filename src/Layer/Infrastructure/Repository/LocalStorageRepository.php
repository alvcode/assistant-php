<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\DTO\Storage\SaveFileDTO;
use App\Layer\Domain\Repository\StorageRepositoryInterface;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;

final readonly class LocalStorageRepository implements StorageRepositoryInterface
{
    public function __construct(
        private Filesystem $filesystem,
        private FileUtilsInterface $fileUtils,
        private ConfigRepositoryInterface $configRepository,
    ) {}

    public function save(SaveFileDTO $in): void
    {
        $this->filesystem->copy(
            $in->getFile()->getRealPath(),
            $this->fileUtils->pathJoin(
                [$this->configRepository->getProjectDir(), $in->getSavePath()],
                true
            ),
        );
    }

    public function getFile(string $path): SplFileInfo
    {
        return new SplFileInfo(
            $this->fileUtils->pathJoin(
                [$this->configRepository->getProjectDir(), $path],
                true
            ),
        );
    }

    public function delete(string $path): void 
    {
        $this->filesystem->remove(
            $this->fileUtils->pathJoin(
                [$this->configRepository->getProjectDir(), $path],
                true
            )
        );
    }

    /** @inheritDoc */
    public function deleteAll(array $paths): void 
    {
        foreach ($paths as $path) {
            $this->delete($path);
        }
    }
}
