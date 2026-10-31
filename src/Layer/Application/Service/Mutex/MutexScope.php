<?php

declare(strict_types=1);

namespace App\Layer\Application\Service\Mutex;

enum MutexScope: string
{
    case DriveArchiveRequestForCreation = 'driveArchiveRequestForCreation';
    case ServiceTest = 'serviceTest';

    public function key(string|int ...$parts): MutexKey
    {
        return MutexKey::for($this, ...$parts);
    }
}
