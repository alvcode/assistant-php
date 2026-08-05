<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\DriveArchive;

use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Domain\Repository\DriveArchiveRepositoryInterface;
use App\Layer\Domain\Repository\DriveStructRepositoryInterface;
use App\Layer\Domain\Repository\QueueRepositoryInterface;
use App\Layer\Domain\Service\Factory\Drive\DriveArchiveFactory;

final readonly class DriveArchiveCreateUseCase
{
    public function __construct(
        private DriveStructRepositoryInterface $driveStructRepository,
        private DriveArchiveRepositoryInterface $driveArchiveRepository,
        private DriveArchiveFactory $driveArchiveFactory,
        private QueueRepositoryInterface $queueRepository,
    ) {}

    /**
     * @param int[] $structIds
     * @throws DriveStructNotFoundException
     */
    public function handle(int $driveArchiveJobId): void
    {

    }
}
