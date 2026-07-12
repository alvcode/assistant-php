<?php

namespace App\Command\Service;

use App\Infrastructure\FormatDict;
use App\Layer\Application\UseCase\DriveRecycleBin\DriveRBForceDeleteOldUseCase;
use App\Layer\Application\UseCase\NoteFile\DeleteNoteFileByIdUseCase;
use App\Layer\Domain\Service\Utils\DateTime;
use App\Layer\Domain\Service\Utils\DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'service:clean-db',
    description: 'Очистка БД от мусора',
)]
class CleanDbCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DeleteNoteFileByIdUseCase $deleteNoteFileUseCase,
        private DriveRBForceDeleteOldUseCase $driveRBForceDeleteOldUseCase,
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
            $this->cleanBlockIP();
            $this->cleanTokens();
            $this->cleanBlockEvents();
            $this->cleanNoteFiles();
            $this->cleanRateLimiter();
            $this->cleanRecycleBin();
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->success('The base has been cleared');
        return Command::SUCCESS;
    }

    private function cleanBlockIP(): void
    {
        $query = "DELETE FROM block_ip WHERE blocked_until < :now_date";
        $conn = $this->entityManager->getConnection();
        $conn->executeQuery($query, [
            'now_date' => DateTime::createNowUtc()->format(FormatDict::DB_DATETIME),
        ]);
    }

    private function cleanTokens(): void
    {
        $query = "DELETE FROM user_tokens WHERE expired_to < :expired_unix";
        $conn = $this->entityManager->getConnection();
        $conn->executeQuery($query, [
            'expired_unix' => DateTime::createNowUtc()->modify('-30 days')->getTimestamp(),
        ]);
    }

    private function cleanBlockEvents(): void
    {
        $query = "DELETE FROM block_events WHERE created_at < :expired_datetime";
        $conn = $this->entityManager->getConnection();
        $conn->executeQuery($query, [
            'expired_datetime' => DateTime::createNowUtc()->modify('-40 minutes')->format(FormatDict::DB_DATETIME),
        ]);
    }

    private function cleanNoteFiles(): void
    {
        $query = "
            select id from files f
            left join file_note_links fnl on fnl.file_id = f.id
            where
            fnl.note_id is null
        ";
        $conn = $this->entityManager->getConnection();
        $stmt = $conn->executeQuery($query);

        foreach ($stmt->iterateAssociative() as $row) {
            $this->deleteNoteFileUseCase->handle($row['id']);
        }
    }

    private function cleanRateLimiter(): void
    {
        $query = "TRUNCATE TABLE rate_limiter";
        $conn = $this->entityManager->getConnection();
        $conn->executeQuery($query);
    }

    private function cleanRecycleBin(): void
    {
        $this->driveRBForceDeleteOldUseCase->handle(
            DateTimeImmutable::createNowUtc()->modify('-30 days')
        );
    }
}
