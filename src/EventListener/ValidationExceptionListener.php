<?php

namespace App\EventListener;

use App\Infrastructure\Lang;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Throwable;

class ValidationExceptionListener
{
    private bool $isDebug;

    public function __construct(bool $isDebug)
    {
        $this->isDebug = $isDebug;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        /** @var Throwable|HttpException $exception */
        $exception = $event->getThrowable();

        $message = $exception->getMessage();

        $status = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($exception instanceof AccessDeniedException || $exception instanceof UnauthorizedHttpException) {
            $event->setResponse(new JsonResponse(
                [
                    'message' => Lang::t('error_you_are_unauthorized'),
                    'status' => 401,
                    'code' => 0,
                ],
                401));
            return;
        }

        if ($exception instanceof HttpException || $this->isDebug) {
            $result = [
                'message' => $message,
                'status' => $status,
                'code' => $exception->getCode(),
            ];
        } else {
            $result = [
                'message' => Lang::t('error_internal_server_error'),
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'code' => 0,
            ];
        }

        if ($this->isDebug) {
            $result['trace'] = $exception->getTrace();
        }

        $response = new JsonResponse($result, $status);
        $event->setResponse($response);
    }
}
