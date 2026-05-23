<?php

declare(strict_types=1);

namespace App\Security;

use App\Attribute\NeedAuth;
use App\Infrastructure\Lang;
use App\Entity\UserEntity;
use App\Layer\Domain\Repository\UserRepositoryInterface;
use App\Request\Auth\TokenRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

final class BearerTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly TokenRequest $tokenRequest,
    ) {}

    public function supports(Request $request): ?bool
    {
        $controller = $request->attributes->get('_controller');
        if (!$controller) {
            return false;
        }

        if (is_string($controller)) {
            $controller = explode('::', $controller);
        }

        return $this->hasNeedAuthAttribute($controller);
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $header = $request->headers->get('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            throw new UnauthorizedHttpException('Bearer', Lang::t('error_you_are_unauthorized'));
        }

        $token = substr($header, 7);

        $this->tokenRequest->token = $token;
        if (!$this->tokenRequest->validate()) {
            throw new UnauthorizedHttpException('Bearer', Lang::t('error_you_are_unauthorized'));
        }

        return new SelfValidatingPassport(
            new UserBadge($token, function ($token): UserEntity {
                $user = $this->userRepository->getByToken($token, time());

                if (!$user) {
                    throw new UnauthorizedHttpException('Bearer', Lang::t('error_you_are_unauthorized'));
                }

                return new UserEntity(
                    id: $user->getId(),
                    login: $user->getLogin(),
                );
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?\Symfony\Component\HttpFoundation\Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, \Throwable $exception): ?\Symfony\Component\HttpFoundation\Response
    {
        return null;
    }

    private function hasNeedAuthAttribute(array $controller): bool
    {
        try {
            $reflectionMethod = new \ReflectionMethod($controller[0], $controller[1]);
            $attributes = $reflectionMethod->getAttributes(NeedAuth::class);

            if (empty($attributes)) {
                return false;
            }

            $needAuthAttr = $attributes[0]->newInstance();
            return $needAuthAttr->required === true;
        } catch (\ReflectionException $e) {
            return false;
        }
    }
}
