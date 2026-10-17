<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Layer\Application\Exception\DriveArchive\DriveArchiveJobNotFoundException;
use App\Layer\Application\UseCase\DriveArchive\DriveArchiveCreateUseCase;
use App\Message\DriveCreateArchiveMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DriveCreateArchiveMessageHandler
{
    public function __construct(
        private DriveArchiveCreateUseCase $driveArchiveCreateUseCase,
    ) {}

    /**
     * @throws DriveArchiveJobNotFoundException
     */
    public function __invoke(DriveCreateArchiveMessage $message): void
    {
        $this->driveArchiveCreateUseCase->handle($message->driveArchiveJobId);
    }
}
