<?php

namespace App\Command\Service;

use App\Layer\Application\UseCase\DriveArchive\DriveArchiveRemoveUseCase;
use App\Layer\Domain\Dict\Drive\DriveArchiveJobStatusEnum;
use App\Layer\Domain\Repository\DriveArchiveRepositoryInterface;
use App\Layer\Domain\Service\Utils\DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'service:clean-archive-jobs',
    description: 'Очистка невостребованных джоб по формированию архива диска',
)]
class CleanArchiveJobsCommand extends Command
{
    public function __construct(
        private readonly DriveArchiveRepositoryInterface $driveArchiveRepository,
        private readonly DriveArchiveRemoveUseCase $archiveRemoveUseCase,
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

        try {
            $driveArchiveJobs = $this->driveArchiveRepository->getJobsByStatusAndFinishedAt(
                status: DriveArchiveJobStatusEnum::Completed,
                finishedAtStart: DateTimeImmutable::createNowUtc()->modify('-1 day'),
                finishedAtEnd: DateTimeImmutable::createNowUtc()->modify('-30 minutes'),
            );

            foreach ($driveArchiveJobs as $driveArchiveJob) {
                $this->archiveRemoveUseCase->handle(
                    driveArchiveJobId: $driveArchiveJob->getId(),
                    userId: $driveArchiveJob->getUserId(),
                );
            }
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->success('The archive jobs has been cleared');
        return Command::SUCCESS;
    }
}
