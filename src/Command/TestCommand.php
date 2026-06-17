<?php

namespace App\Command;

use App\Layer\Domain\Service\Utils\FileUtilsInterface;
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
    public function __construct(
        private FileUtilsInterface $fileUtils
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->text($this->fileUtils->getExtensionByName('plug.pdf'));

        $io->success('end');

        return Command::SUCCESS;
    }
}
