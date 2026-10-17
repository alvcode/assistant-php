<?php

namespace App\Command\Cron;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'cron:every-hour',
    description: 'Задачи, выполняемые каждый час',
)]
class EveryHourCommand extends AbstractCronCommand
{
    protected function getCommands(): array
    {
        return [
        ];
    }
}
