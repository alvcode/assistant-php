<?php

namespace App\Command\Cron;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'cron:every-five-minutes',
    description: 'Задачи, выполняемые каждые 5 минут',
)]
class EveryFiveMinutesCommand extends AbstractCronCommand
{
    protected function getCommands(): array
    {
        return [
        ];
    }
}
