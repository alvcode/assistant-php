<?php

declare(strict_types=1);

namespace App\Layer\Application\Service\Mutex;

interface MutexInterface
{
    public function createLock(MutexKey $key): MutexLockInterface;
}
