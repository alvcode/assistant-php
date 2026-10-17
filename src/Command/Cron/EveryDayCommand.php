<?php

namespace App\Command\Cron;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'cron:every-day',
    description: 'Задачи, выполняемые каждый день',
)]
class EveryDayCommand extends AbstractCronCommand
{
    protected function getCommands(): array
    {
        return [

        ];
    }
}
