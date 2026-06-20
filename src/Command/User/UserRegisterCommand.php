<?php

namespace App\Command\User;

use App\Layer\Application\DTO\User\LoginAndPasswordDTO;
use App\Layer\Application\UseCase\User\RegisterByLoginUseCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'user:register',
    description: 'Регистрация нового юзера',
)]
class UserRegisterCommand extends Command
{
    public function __construct(
        private RegisterByLoginUseCase $registerUseCase
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('login', InputArgument::REQUIRED, 'User login')
        ->addArgument('password', InputArgument::REQUIRED, 'User password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $login = $input->getArgument('login');
        $password = $input->getArgument('password');

        try {
            $this->registerUseCase->handle(new LoginAndPasswordDTO(login: $login, password: $password));
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->success('A new user was created');
        return Command::SUCCESS;
    }
}
