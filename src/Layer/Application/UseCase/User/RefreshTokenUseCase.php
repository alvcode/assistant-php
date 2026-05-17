<?php

declare(strict_types=1);

namespace App\Layer\Application\UseCase\User;

use App\Layer\Application\DTO\User\RefreshTokenDTO;
use App\Layer\Application\Exception\User\RefreshTokenNotFoundException;
use App\Layer\Domain\Entity\UserTokenEntity;
use App\Layer\Domain\Repository\UserTokenRepositoryInterface;
use App\Layer\Domain\Service\Factory\User\UserFactory;

final readonly class RefreshTokenUseCase
{
    public function __construct(
        private UserTokenRepositoryInterface $userTokenRepository,
        private UserFactory $userFactory,
    ) {}

    /**
     * @throws RefreshTokenNotFoundException
     */
    public function handle(RefreshTokenDTO $in): UserTokenEntity
    {
        $userTokenEntity = $this->userTokenRepository->getByTokenAndRefreshToken($in->token, $in->refreshToken);
        if (!$userTokenEntity) {
            throw new RefreshTokenNotFoundException('Токен не найден');
        }

        return $this->userTokenRepository->save($this->userFactory->getNewToken($userTokenEntity->getUserId()));
    }
}
