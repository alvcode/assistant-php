<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\User;

use App\Layer\Application\Exception\User\InvalidCredentialsException;
use App\Layer\Application\Exception\User\UserNotFoundException;
use App\Layer\Domain\Repository\UserRepositoryInterface;
use App\Layer\Domain\Service\Utils\DateTime;
use App\Layer\Domain\Service\Utils\HasherServiceInterface;

final readonly class UserChangePasswordUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private HasherServiceInterface $hasherService,

    ) {}

    /**
     * @throws UserNotFoundException
     * @throws InvalidCredentialsException
     */
    public function handle(string $oldPassword, string $newPassword, int $userId): void
    {
        $userEntity = $this->userRepository->getById($userId);
        if (!$userEntity) {
            throw new UserNotFoundException('Пользователь не найден');
        }

        $isPasswordValid = $this->hasherService->isUserPasswordValid($userEntity, $oldPassword);
        if (!$isPasswordValid) {
            throw new InvalidCredentialsException('Неверный пароль');
        }

        $userEntity->setPassword($this->hasherService->hashUserPassword($newPassword));
        $userEntity->setUpdatedAt(DateTime::createNowUtc());
        $this->userRepository->save($userEntity);
    }
}
