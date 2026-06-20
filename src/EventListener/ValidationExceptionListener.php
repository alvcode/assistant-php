<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Infrastructure\Lang;
use App\Security\BlockEvent\BlockEventService;
use App\Security\BlockEvent\BlockEventTypeEnum;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Throwable;

final readonly class ValidationExceptionListener
{
    public function __construct(
        private bool $isDebug,
        private BlockEventService $blockEventService,
        private LoggerInterface $logger, 
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

        $this->logger->error($exception->getMessage(), [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'trace' => $exception->getTraceAsString(),
        ]);

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
        $exception = $event->getThrowable();
        if ($exception instanceof AccessDeniedHttpException) {
            return;
        }

        $type = match (true) {
            $exception instanceof NotFoundHttpException => BlockEventTypeEnum::NotFound,
            $exception instanceof BadRequestHttpException => BlockEventTypeEnum::DecodeBody,
            $exception instanceof UnauthorizedHttpException => BlockEventTypeEnum::Unauthorized,
            default => null
        };
        if ($type) {
            $this->blockEventService->setEvent($event->getRequest(), $type);
        }
        $this->blockEventService->setEvent($event->getRequest(), BlockEventTypeEnum::Other);
    }
}
