<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Infrastructure\Lang;
use App\Security\BlockEvent\BlockEventService;
use App\Security\BlockEvent\BlockEventTypeEnum;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Throwable;

final class ValidationExceptionListener
{
    public function __construct(
        private readonly bool $isDebug,
        private readonly BlockEventService $blockEventService,
    )
    {
    }

    /**
     * @throws Exception
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        /** @var Throwable|HttpException $exception */
        $exception = $event->getThrowable();

        $message = $exception->getMessage();

        $status = method_exists($exception, 'getStatusCode')
            ? $exception->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;

        $this->handleBlockEvents($event);

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

    /**
     * @throws Exception
     */
    private function handleBlockEvents(ExceptionEvent $event): void
    {
        if ($event->getThrowable() instanceof AccessDeniedHttpException) {
            return;
        }
        if ($event->getThrowable() instanceof BadRequestHttpException) {
            $this->blockEventService->setEvent($event->getRequest(), BlockEventTypeEnum::DecodeBody);
        }
        if ($event->getThrowable() instanceof UnauthorizedHttpException) {
            $this->blockEventService->setEvent($event->getRequest(), BlockEventTypeEnum::Unauthorized);
        }
        $this->blockEventService->setEvent($event->getRequest(), BlockEventTypeEnum::Other);
    }
}
