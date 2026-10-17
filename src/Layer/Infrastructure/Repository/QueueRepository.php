<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Repository\QueueRepositoryInterface;
use App\Message\DriveCreateArchiveMessage;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class QueueRepository implements QueueRepositoryInterface
{
    public function __construct(
        private MessageBusInterface $bus,
    ) {}

    /**
     * @throws ExceptionInterface
     */
    public function pushDriveArchive(int $driveArchiveJobId): void
    {
        $this->bus->dispatch(new DriveCreateArchiveMessage($driveArchiveJobId));
    }
}
