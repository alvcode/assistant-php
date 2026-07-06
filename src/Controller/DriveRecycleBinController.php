<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\NeedAuth;
use App\Entity\UserEntity;
use App\Infrastructure\Lang;
use App\Layer\Application\Exception\DriveRecycleBin\DriveRecycleBinNotFoundException;
use App\Layer\Application\UseCase\DriveRecycleBin\DriveRBGetAllUseCase;
use App\Layer\Application\UseCase\DriveRecycleBin\DriveRBRestoreAllUseCase;
use App\Layer\Application\UseCase\DriveRecycleBin\DriveRBRestoreOneUseCase;
use App\Layer\Domain\Exception\AbstractLogicException;
use App\Request\Common\IDRequest;
use App\Response\DriveRecycleBin\DriveRBGetAllResponse;
use App\Security\BlockEvent\BlockEventService;
use App\Security\BlockEvent\BlockEventTypeEnum;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class DriveRecycleBinController extends AbstractController
{
    public function __construct(
        private readonly BlockEventService $blockEventService,
    ) {}

    /**
     * @throws Exception
     */
    #[Route(path: '/api/drive-recycle-bin', name: 'drive_recycle_bin.get_all', methods: ['GET'])]
    #[NeedAuth]
    public function create(
        DriveRBGetAllUseCase $useCase,
    ): JsonResponse
    {
        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            return new JsonResponse(
                DriveRBGetAllResponse::fromDriveRBAggregates($useCase->handle($user->id)),
                Response::HTTP_OK
            );
        } catch (AbstractLogicException $e) {
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/api/drive-recycle-bin/restore-one/{id}', name: 'drive_recycle_bin.restore_one', methods: ['POST'])]
    #[NeedAuth]
    public function restoreOne(
        int $id,
        Request $request,
        IDRequest $requestModel,
        DriveRBRestoreOneUseCase $useCase
    ): Response
    {
        $requestModel->id = $id;
        if (!$requestModel->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $useCase->handle($requestModel->id, $user->id);
            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (AbstractLogicException $e) {
            if ($e instanceof DriveRecycleBinNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/drive-recycle-bin/restore-all', name: 'drive_recycle_bin.restore_all', methods: ['POST'])]
    #[NeedAuth]
    public function restoreAll(DriveRBRestoreAllUseCase $useCase): Response
    {
        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $useCase->handle($user->id);
            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (AbstractLogicException $e) {
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    public function forceDeleteOne()
    {

    }

    public function forceDeleteAll()
    {

    }
}
