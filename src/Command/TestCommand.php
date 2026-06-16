<?php

namespace App\Command;

use App\Layer\Infrastructure\Repository\Helper\ArrayHelperTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'test',
    description: 'Command for test',
)]
class TestCommand extends Command
{
    use ArrayHelperTrait;

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $structIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
        foreach ($this->arrayChunk($structIds, 5) as $batch) {
            $io->text($batch);
            $io->text('-----------');
        }

        $io->success('end');

        return Command::SUCCESS;
    }
}
