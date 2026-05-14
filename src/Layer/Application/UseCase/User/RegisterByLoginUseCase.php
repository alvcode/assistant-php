<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\User;

use App\Layer\Application\DTO\User\LoginAndPasswordDTO;
use App\Layer\Application\DTO\User\UserDTO;
use App\Layer\Application\Exception\User\UserAlreadyExistsException;
use App\Layer\Domain\Repository\UserRepositoryInterface;
use App\Layer\Domain\Service\Factory\User\UserFactory;

final readonly class RegisterByLoginUseCase
{
    public function __construct(
        private UserFactory $userFactory,
        private UserRepositoryInterface $userRepository,
    ) {}

    /**
     * @throws UserAlreadyExistsException
     */
    public function handle(LoginAndPasswordDTO $in): UserDTO
    {
        $existsLogin = $this->userRepository->existsByLogin($in->login);
        if ($existsLogin) {
            throw new UserAlreadyExistsException('User already exists');
        }

        $userEntity = $this->userRepository->save($this->userFactory->getNewUser($in->login, $in->password));
        return new UserDTO(
            id: $userEntity->getId(),
            login: $in->login,
            createdAt: $userEntity->getCreatedAt(),
            updatedAt: $userEntity->getUpdatedAt(),
        );
    }
}
