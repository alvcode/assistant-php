<?php

declare(strict_types=1);

namespace App\Layer\Application\Exception\DriveArchive;

use App\Layer\Domain\Exception\AbstractLogicException;

class DriveArchiveExistsActiveJobsException extends AbstractLogicException
{
    public function getErrorKey(): string
    {
        return 'error_drive_archive_exists_active_jobs';
    }
}
