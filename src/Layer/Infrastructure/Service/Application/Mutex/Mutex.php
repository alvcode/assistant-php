<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Service\Application\Mutex;

use App\Layer\Application\Service\Mutex\MutexInterface;
use App\Layer\Application\Service\Mutex\MutexLockInterface;
use Symfony\Component\Lock\LockFactory;

final readonly class Mutex implements MutexInterface
{
    public function __construct(
        private LockFactory $lockFactory,
    ) {}

    public function createLock(string $key): MutexLockInterface
    {
        return new MutexLock($this->lockFactory->createLock($key));
    }
}
