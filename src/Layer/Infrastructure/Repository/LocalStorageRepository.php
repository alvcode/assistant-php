<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Repository\DTO\Storage\SaveFileDTO;
use App\Layer\Domain\Repository\StorageRepositoryInterface;
use App\Layer\Domain\ValueObject\FileContentVO;
use Symfony\Component\Filesystem\Filesystem;

final readonly class LocalStorageRepository implements StorageRepositoryInterface
{
    public function __construct(
        private Filesystem $filesystem,
    ) {}

    public function save(SaveFileDTO $in): void
    {
        $this->filesystem->dumpFile(
            $in->getSavePath(),
            $in->getFile()->getContent(),
        );
    }

    public function getFile(string $path): FileContentVO
    {
        return new FileContentVO(file_get_contents($path));
    }
}
