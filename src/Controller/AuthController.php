<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\NeedAuth;
use App\Entity\UserEntity;
use App\Infrastructure\Lang;
use App\Layer\Application\DTO\User\LoginAndPasswordDTO;
use App\Layer\Application\DTO\User\RefreshTokenDTO;
use App\Layer\Application\Exception\User\InvalidCredentialsException;
use App\Layer\Application\Exception\User\RefreshTokenNotFoundException;
use App\Layer\Application\UseCase\User\LoginUserUseCase;
use App\Layer\Application\UseCase\User\RefreshTokenUseCase;
use App\Layer\Application\UseCase\User\RegisterByLoginUseCase;
use App\Layer\Application\UseCase\User\UserChangePasswordUseCase;
use App\Layer\Domain\Exception\AbstractLogicException;
use App\Request\Auth\ChangePasswordRequest;
use App\Request\Auth\RefreshTokenRequest;
use App\Request\Auth\UserLoginAndPasswordRequest;
use App\Response\User\UserResponse;
use App\Response\User\UserTokenResponse;
use App\Security\BlockEvent\BlockEventService;
use App\Security\BlockEvent\BlockEventTypeEnum;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class AuthController extends AbstractController
{
    public function __construct(
        private BlockEventService $blockEventService,
        private ParameterBagInterface $parameterBag,
    ) {}

    /**
     * @throws Exception
     */
    #[Route(path: '/api/auth/register', name: 'auth.register', methods: ['POST'])]
    public function register(
        Request $request,
        UserLoginAndPasswordRequest $requestModel,
        RegisterByLoginUseCase $useCase,
    ): JsonResponse
    {
        if ($this->parameterBag->get('app.registerNewUserViaApi') === 'false') {
            throw new UnprocessableEntityHttpException(Lang::t('error_operation_unavailable'));
        }

        if (!$requestModel->populateByRequest($request)->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
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

    /**
     * @throws Exception
     */
    #[Route(path: '/api/auth/login', name: 'auth.login', methods: ['POST'])]
    public function login(
        Request $request,
        UserLoginAndPasswordRequest $requestModel,
        LoginUserUseCase $useCase,
    ): JsonResponse
    {
        if (!$requestModel->populateByRequest($request)->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
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
            if ($e instanceof InvalidCredentialsException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::SignIn);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/api/auth/refresh-token', name: 'auth.refresh_token', methods: ['POST'])]
    public function refreshToken(
        Request $request,
        RefreshTokenRequest $requestModel,
        RefreshTokenUseCase $useCase,
    ): JsonResponse
    {
        if (!$requestModel->populateByRequest($request)->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        try {
            $userTokenEntity = $useCase->handle(
                new RefreshTokenDTO(token: $requestModel->token, refreshToken: $requestModel->refresh_token)
            );

            return new JsonResponse(
                UserTokenResponse::fromUserTokenEntity($userTokenEntity),
                Response::HTTP_CREATED
            );
        } catch (AbstractLogicException $e) {
            if ($e instanceof RefreshTokenNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::RefreshToken);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/user/change-password', name: 'auth.change_password', methods: ['PATCH'])]
    #[NeedAuth]
    public function changePassword(
        Request $request,
        ChangePasswordRequest $requestModel,
        UserChangePasswordUseCase $useCase
    ): Response
    {
        if (!$requestModel->populateByRequest($request)->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $useCase->handle(
                oldPassword: $requestModel->current_password,
                newPassword: $requestModel->new_password,
                userId: $user->id
            );
            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (AbstractLogicException $e) {
            if ($e instanceof InvalidCredentialsException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }
}
