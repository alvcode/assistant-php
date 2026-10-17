<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use App\Layer\Domain\Repository\DTO\Storage\SaveFileDTO;
use App\Layer\Domain\Repository\StorageRepositoryInterface;
use App\Layer\Domain\Service\Utils\FileUtilsInterface;
use App\Layer\Domain\ValueObject\PathVO;
use SplFileInfo;
use Symfony\Component\Filesystem\Filesystem;
use App\Layer\Domain\ValueObject\SplFileInfoVO;

final readonly class LocalStorageRepository implements StorageRepositoryInterface
{
    public function __construct(
        private Filesystem $filesystem,
        private FileUtilsInterface $fileUtils,
        private ConfigRepositoryInterface $configRepository,
    ) {}

    public function save(SaveFileDTO $in): void
    {
        $link = $in->getSavePath()->isAbsolute() ? $in->getSavePath()->getPath() : $this->fileUtils->pathJoin(
            [$this->configRepository->getProjectDir(), $in->getSavePath()->getPath()],
            true
        );
        $this->filesystem->copy(
            $in->getFile()->getRealPath(),
            $link
        );
    }

    public function getFile(PathVO $path): SplFileInfoVO
    {
        $link = $path->isAbsolute() ? $path->getPath() : $this->fileUtils->pathJoin(
            [$this->configRepository->getProjectDir(), $path->getPath()],
            true
        );
        return new SplFileInfoVO(
            file: new SplFileInfo($link),
            isTemporary: false
        );
    }

    public function delete(PathVO $path): void
    {
        $link = $path->isAbsolute() ? $path->getPath() : $this->fileUtils->pathJoin(
            [$this->configRepository->getProjectDir(), $path->getPath()],
            true
        );
        $this->filesystem->remove($link);
    }

    /** @inheritDoc */
    public function deleteAll(array $paths): void
    {
        foreach ($paths as $path) {
            $this->delete($path);
        }
    }

    public function isExists(PathVO $path): bool
    {
        $link = $path->isAbsolute() ? $path->getPath() : $this->fileUtils->pathJoin(
            [$this->configRepository->getProjectDir(), $path->getPath()],
            true
        );
        return $this->filesystem->exists($link);
    }
}
