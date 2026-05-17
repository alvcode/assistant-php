<?php

declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\Lang;
use App\Layer\Application\DTO\User\LoginAndPasswordDTO;
use App\Layer\Application\UseCase\User\LoginUserUseCase;
use App\Layer\Application\UseCase\User\RegisterByLoginUseCase;
use App\Layer\Domain\Exception\AbstractLogicException;
use App\Request\Auth\UserLoginAndPasswordRequest;
use App\Response\User\UserResponse;
use App\Response\User\UserTokenResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class AuthController extends AbstractController
{
    #[Route(path: '/api/auth/register', name: 'auth.register', methods: ['POST'])]
    public function register(
        Request $request,
        UserLoginAndPasswordRequest $requestModel,
        RegisterByLoginUseCase $useCase,
    ): JsonResponse
    {
        if (!$requestModel->populateByRequest($request)->validate()) {
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        try {
            $userDTO = $useCase->handle(
                new LoginAndPasswordDTO(login: $requestModel->login, password: $requestModel->password)
            );

            return new JsonResponse(UserResponse::fromUserDTO($userDTO), Response::HTTP_CREATED);
        } catch (AbstractLogicException $e) {
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/auth/login', name: 'auth.login', methods: ['POST'])]
    public function login(
        Request $request,
        UserLoginAndPasswordRequest $requestModel,
        LoginUserUseCase $useCase,
    ): JsonResponse
    {
        if (!$requestModel->populateByRequest($request)->validate()) {
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        try {
            $userTokenEntity = $useCase->handle(
                new LoginAndPasswordDTO(login: $requestModel->login, password: $requestModel->password)
            );

            return new JsonResponse(
                UserTokenResponse::fromUserTokenEntity($userTokenEntity),
                Response::HTTP_CREATED
            );
        } catch (AbstractLogicException $e) {
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }
}
