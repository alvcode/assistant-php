<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\NeedAuth;
use App\Entity\UserEntity;
use App\Infrastructure\Lang;
use App\Layer\Application\Exception\Common\ShareLinkNotFoundException;
use App\Layer\Application\Exception\Drive\DriveShareNotFoundException;
use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Application\UseCase\DriveShare\DriveShareCreateUseCase;
use App\Layer\Application\UseCase\DriveShare\DriveShareDeleteUseCase;
use App\Layer\Application\UseCase\DriveShare\DriveShareGetShareUseCase;
use App\Layer\Application\UseCase\DriveShare\DriveShareGetStructByHashUseCase;
use App\Layer\Domain\Exception\AbstractLogicException;
use App\Request\Common\ShareHashRequest;
use App\Response\Drive\DriveShareResponse;
use App\Security\BlockEvent\BlockEventService;
use App\Security\BlockEvent\BlockEventTypeEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class DriveShareController extends AbstractController
{
    public function __construct(
        private readonly BlockEventService $blockEventService,
    )
    {
    }

    #[Route(path: '/api/drive/files/{id}/share', name: 'drive.share_create', methods: ['POST'])]
    #[NeedAuth]
    public function share(int $id, Request $request, DriveShareCreateUseCase $useCase): JsonResponse
    {
        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $driveShareEntity = $useCase->handle($id, $user->id);
            return new JsonResponse(
                DriveShareResponse::fromNoteShareEntity($driveShareEntity),
                Response::HTTP_CREATED
            );
        } catch (AbstractLogicException $e) {
            if ($e instanceof DriveStructNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/drive/files/{id}/share', name: 'drive.share_get', methods: ['GET'])]
    #[NeedAuth]
    public function getShare(int $id, Request $request, DriveShareGetShareUseCase $useCase): JsonResponse
    {
        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $noteShareEntity = $useCase->handle($id, $user->id);
            return new JsonResponse(
                DriveShareResponse::fromNoteShareEntity($noteShareEntity),
                Response::HTTP_CREATED
            );
        } catch (AbstractLogicException $e) {
            if ($e instanceof DriveStructNotFoundException || $e instanceof ShareLinkNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/drive/files/{id}/share', name: 'drive.share_delete', methods: ['DELETE'])]
    #[NeedAuth]
    public function deleteShare(int $id, Request $request, DriveShareDeleteUseCase $useCase): Response
    {
        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $useCase->handle($id, $user->id);
            return new Response(null, Response::HTTP_CREATED);
        } catch (AbstractLogicException $e) {
            if ($e instanceof DriveStructNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/drive-share/{hash}/detail-item', name: 'drive_share.get_detail_item_by_hash', methods: ['GET'])]
    public function getTreeItemByHash(
        string $hash,
        ShareHashRequest $requestModel,
        Request $request,
        DriveShareGetStructByHashUseCase $getStructByHashUseCase,
    ): Response
    {
        $requestModel->hash = $hash;
        if (!$requestModel->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        try {
            $structEntity = $getStructByHashUseCase->handle($requestModel->hash);

            var_dump($structEntity);
            exit();
        } catch (AbstractLogicException $e) {
            if ($e instanceof DriveShareNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
        }

        var_dump($hash);
        exit();
    }
}
