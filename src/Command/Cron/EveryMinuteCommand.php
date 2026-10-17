<?php

namespace App\Command\Cron;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'cron:every-minute',
    description: 'Задачи, выполняемые каждую минуту',
)]
class EveryMinuteCommand extends AbstractCronCommand
{
    protected function getCommands(): array
    {
        return [
        ];
    }
}
