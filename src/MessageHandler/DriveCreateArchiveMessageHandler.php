<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\DriveCreateArchiveMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DriveCreateArchiveMessageHandler
{
    public function __invoke(DriveCreateArchiveMessage $message): void
    {
        // здесь архивируешь файлы
        sleep(5);
    }
}
