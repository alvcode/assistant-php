<?php

declare(strict_types=1);

namespace App\Layer\Application\Service\Mutex;

interface MutexLockInterface
{
    public function acquire(): bool;

    public function acquireWithWaiting(float $timeoutSeconds): bool;

    public function release(): void;
}
