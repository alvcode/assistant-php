<?php

declare(strict_types=1);

namespace App\Layer\Domain\ValueObject\ClientNotification;

final readonly class CNArchiveStatusUpdateVO implements ClientNotificationInterface
{
    public function __construct(
        private int $userId,
        private int $jobId,
        private int $status,
        private bool $existsArchive,
    ) {}

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getEventKey(): string
    {
        return 'archiveStatusUpdate';
    }

    /** @inheritDoc */
    public function getPayload(): array
    {
        return [
            'id' => $this->jobId,
            'status' => $this->status,
            'existsArchive' => $this->existsArchive,
        ];
    }

    public function getLifetimeSeconds(): int
    {
        return 3;
    }
}
