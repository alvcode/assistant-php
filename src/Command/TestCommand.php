<?php

namespace App\Command;

use App\Layer\Domain\Dict\Drive\DriveArchiveJobPlaceEnum;
use App\Layer\Domain\Dict\Drive\DriveArchiveJobStatusEnum;
use App\Layer\Domain\Repository\DriveArchiveRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'service:test',
    description: 'тестовая команда',
)]
class TestCommand extends Command
{
    public function __construct(
        private DriveArchiveRepositoryInterface $driveArchiveRepository,
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

        $exists = $this->driveArchiveRepository->existsJobsByUserAndStatuses(
            1,
            [
                DriveArchiveJobStatusEnum::New,
                DriveArchiveJobStatusEnum::Processed,
                DriveArchiveJobStatusEnum::Completed,
                DriveArchiveJobStatusEnum::Deleted
            ],
            DriveArchiveJobPlaceEnum::Common,
            [1, 7]
        );

        $io->info($exists ? 'yes' : 'no');

        $io->success('The archive jobs has been cleared');
        return Command::SUCCESS;
    }
}
