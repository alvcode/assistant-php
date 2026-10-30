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

    public function acquireWithWaiting($timeoutSeconds): bool
    {
        $now = time();
        while (!$this->sharedLock->acquire(true)) {
            if ((time() - $now) > $timeoutSeconds) {
                return false;
            }
            sleep(1);
        }
        return true;
    }

    public function release(): void
    {
        $this->sharedLock->release();
    }
}
