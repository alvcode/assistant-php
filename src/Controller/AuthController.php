<?php

declare(strict_types=1);

namespace App\Controller;

use App\Layer\Application\DTO\User\LoginAndPasswordDTO;
use App\Layer\Application\UseCase\User\RegisterByLoginUseCase;
use App\Request\Auth\UserLoginAndPasswordRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    )
    {
        if (!$requestModel->populateByRequest($request)->validate()) {
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        $useCase->handle(new LoginAndPasswordDTO(login: $requestModel->login, password: $requestModel->password));
        var_dump($requestModel->login);
        var_dump($requestModel->password);
        exit();
    }
}
