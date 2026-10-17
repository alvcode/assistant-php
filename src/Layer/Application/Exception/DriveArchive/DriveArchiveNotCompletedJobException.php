<?php

declare(strict_types=1);

namespace App\Layer\Application\Exception\DriveArchive;

use App\Layer\Domain\Exception\AbstractLogicException;

class DriveArchiveNotCompletedJobException extends AbstractLogicException
{
    public function getErrorKey(): string
    {
        return 'error_drive_archive_not_completed_job';
    }
}
