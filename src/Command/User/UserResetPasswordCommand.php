<?php

namespace App\Command\User;

use App\Layer\Application\DTO\User\LoginAndPasswordDTO;
use App\Layer\Application\UseCase\User\UserResetPasswordWithoutCurrentUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'user:reset-password',
    description: 'Сброс пароля пользователя',
)]
class UserResetPasswordCommand extends Command
{
    public function __construct(
        private UserResetPasswordWithoutCurrentUseCase $useCase
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('login', InputArgument::REQUIRED, 'User login')
        ->addArgument('password', InputArgument::REQUIRED, 'New user password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $login = $input->getArgument('login');
        $password = $input->getArgument('password');

        $this->useCase->handle(new LoginAndPasswordDTO(login: $login, password: $password));

        $io->success('The password has been changed');
        return Command::SUCCESS;
    }
}
