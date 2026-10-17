<?php

namespace App\Command\Cron;

use App\Command\Cron\DTO\CronCommandDTO;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'cron:every-thirty-minutes',
    description: 'Задачи, выполняемые каждые 30 минут',
)]
class EveryThirtyMinutesCommand extends AbstractCronCommand
{
    protected function getCommands(): array
    {
        return [
            new CronCommandDTO(name: "service:clean-archive-jobs")
        ];
    }
}
