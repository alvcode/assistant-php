<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\User;

use App\Layer\Application\DTO\User\LoginAndPasswordDTO;
use App\Layer\Application\Exception\User\InvalidCredentialsException;
use App\Layer\Domain\Entity\UserTokenEntity;
use App\Layer\Domain\Repository\UserRepositoryInterface;
use App\Layer\Domain\Repository\UserTokenRepositoryInterface;
use App\Layer\Domain\Service\Factory\User\UserFactory;
use App\Layer\Domain\Service\Utils\HasherServiceInterface;

final readonly class LoginUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private HasherServiceInterface $hasherService,
        private UserFactory $userFactory,
        private UserTokenRepositoryInterface $userTokenRepository,
    ) {}

    /**
     * @throws InvalidCredentialsException
     */
    public function handle(LoginAndPasswordDTO $in): UserTokenEntity
    {
        $userEntity = $this->userRepository->getByLogin($in->login);
        if (!$userEntity) {
            throw new InvalidCredentialsException('Пользователь не найден');
        }

        $isPasswordValid = $this->hasherService->isUserPasswordValid($userEntity, $in->password);
        if (!$isPasswordValid) {
            throw new InvalidCredentialsException('Неверный пароль');
        }

        return $this->userTokenRepository->create($this->userFactory->getNewToken($userEntity->getId()));
    }
}
