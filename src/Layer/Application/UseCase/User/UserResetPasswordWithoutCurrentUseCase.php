<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\User;

use App\Layer\Application\DTO\User\LoginAndPasswordDTO;
use App\Layer\Application\Exception\User\UserNotFoundException;
use App\Layer\Domain\Repository\UserRepositoryInterface;
use App\Layer\Domain\Service\Utils\DateTime;
use App\Layer\Domain\Service\Utils\HasherServiceInterface;

final readonly class UserResetPasswordWithoutCurrentUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private HasherServiceInterface $hasherService,
    ) {}

    /**
     * @throws UserNotFoundException
     */
    public function handle(LoginAndPasswordDTO $in): void
    {
        $userEntity = $this->userRepository->getByLogin($in->login);
        if (!$userEntity) {
            throw new UserNotFoundException('Пользователь не найден');
        }

        $userEntity->setPassword($this->hasherService->hashUserPassword($in->password));
        $userEntity->setUpdatedAt(DateTime::createNowUtc());
        $this->userRepository->save($userEntity);
    }
}
