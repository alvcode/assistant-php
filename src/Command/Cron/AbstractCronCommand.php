<?php

namespace App\Command\Cron;

use App\Command\Cron\DTO\CronCommandDTO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractCronCommand extends Command
{
    /**
     * @return CronCommandDTO[]
     */
    abstract protected function getCommands(): array;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->getCommands() as $subCommand) {
            $name = $subCommand->name;
            $arguments = $subCommand->arguments ?? [];
            $options = $subCommand->options ?? [];

            $io->section(sprintf('Running: %s', $name));

            $status = $this->runSubCommand($name, $arguments, $options, $output);

            if ($status !== Command::SUCCESS) {
                $io->error(sprintf('Command "%s" failed with status %d', $name, $status));
                return Command::FAILURE;
            }
        }

        $io->success('All cron commands completed');

        return Command::SUCCESS;
    }

    /**
     * @param array<string, mixed> $arguments
     * @param array<string, mixed> $options
     * @throws ExceptionInterface
     */
    protected function runSubCommand(
        string $name,
        array $arguments = [],
        array $options = [],
        ?OutputInterface $output = null,
    ): int {
        $command = $this->getApplication()?->find($name);

        if ($command === null) {
            throw new \RuntimeException(sprintf('Command "%s" not found', $name));
        }

        $subInput = new ArrayInput(array_merge(['command' => $name], $arguments, $options));
        $subInput->setInteractive(false);

        return $command->run($subInput, $output);
    }
}
