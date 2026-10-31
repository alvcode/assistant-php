<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Service\Application\Mutex;

use App\Layer\Application\Service\Mutex\MutexLockInterface;
use Symfony\Component\Lock\SharedLockInterface;

final readonly class MutexLock implements MutexLockInterface
{
    public function __construct(private SharedLockInterface $sharedLock) {}

    public function acquire(): bool
    {
        return $this->sharedLock->acquire();
    }

    public function acquireWithWaiting(float $timeoutSeconds): bool
    {
        $deadline = microtime(true) + max(0.0, $timeoutSeconds);

        do {
            if ($this->sharedLock->acquire()) {
                return true;
            }

            if (microtime(true) >= $deadline) {
                return false;
            }

            usleep(100_000);
        } while (true);
    }

    public function release(): void
    {
        $this->sharedLock->release();
    }
}
