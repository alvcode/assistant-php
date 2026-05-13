<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\User;

use App\Layer\Application\DTO\User\LoginAndPasswordDTO;
use App\Layer\Domain\Repository\UserRepositoryInterface;
use App\Layer\Domain\Service\Factory\User\CreateUserFactory;

final readonly class RegisterByLoginUseCase
{
    public function __construct(
        private CreateUserFactory $createUserFactory,
        private UserRepositoryInterface $userRepository,
    ) {}

    public function handle(LoginAndPasswordDTO $in)
    {
        $existsLogin = $this->userRepository->existsByLogin($in->login);
        if ($existsLogin) {

        }
        var_dump($existsLogin);
        exit();
        $userEntity = $this->createUserFactory->getNewUser($in->login, $in->password);

        var_dump($userEntity);
        exit();
    }
}
