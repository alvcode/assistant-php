<?php

namespace App\Command\Service;

use App\Layer\Application\UseCase\Drive\DriveRelocationStorageUseCase;
use App\Layer\Application\UseCase\NoteFile\NoteFileRelocationStorageUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'service:relocation-storage',
    description: 'Перемещение файлов хранилища из s3 в local и обратно',
)]
class RelocationStorageCommand extends Command
{
    public function __construct(
        private DriveRelocationStorageUseCase $driveRelocationStorageUseCase,
        private NoteFileRelocationStorageUseCase $noteFileRelocationStorageUseCase,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('to-s3', null, InputOption::VALUE_NEGATABLE);
        $this->addOption('to-local', null, InputOption::VALUE_NEGATABLE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $toS3 = $input->getOption('to-s3') == 1;
        $toLocal = $input->getOption('to-local') == 1;

        if (!$toS3 && !$toLocal) {
            $io->error('You need to specify where to move the storage: --to-s3 or --to-local');
            return Command::FAILURE;
        }

        $this->noteFileRelocationStorageUseCase->handle($toLocal);
        $this->driveRelocationStorageUseCase->handle($toLocal);

        $io->success('The storage was moved');
        return Command::SUCCESS;
    }
}
