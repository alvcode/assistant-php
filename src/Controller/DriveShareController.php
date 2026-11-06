<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\NeedAuth;
use App\Entity\UserEntity;
use App\Infrastructure\Lang;
use App\Layer\Application\Exception\Common\ShareLinkNotFoundException;
use App\Layer\Application\Exception\Drive\DriveFileNotFoundException;
use App\Layer\Application\Exception\Drive\DriveShareNotFoundException;
use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Application\Exception\DriveArchive\DriveArchiveExistsActiveJobsException;
use App\Layer\Application\UseCase\Drive\DriveGetChunkByNumberUseCase;
use App\Layer\Application\UseCase\Drive\DriveGetChunksInfoUseCase;
use App\Layer\Application\UseCase\Drive\DriveGetFileUseCase;
use App\Layer\Application\UseCase\Drive\DriveGetStructFullSizeUseCase;
use App\Layer\Application\UseCase\Drive\DriveGetTreeItemUseCase;
use App\Layer\Application\UseCase\DriveArchive\DriveArchiveRequestForCreationUseCase;
use App\Layer\Application\UseCase\DriveShare\DriveShareCreateUseCase;
use App\Layer\Application\UseCase\DriveShare\DriveShareDeleteUseCase;
use App\Layer\Application\UseCase\DriveShare\DriveShareGetShareUseCase;
use App\Layer\Application\UseCase\DriveShare\DriveShareGetStructByHashUseCase;
use App\Layer\Domain\Dict\Drive\DriveArchiveJobPlaceEnum;
use App\Layer\Domain\Exception\AbstractLogicException;
use App\Request\Common\IDRequest;
use App\Request\Common\ShareHashRequest;
use App\Response\Drive\DriveChunksInfoResponse;
use App\Response\Drive\DriveGetItemFullSizeResponse;
use App\Response\Drive\DriveShareResponse;
use App\Response\Drive\DriveTreeResponse;
use App\Response\DriveArchive\DriveArchiveJobResponse;
use App\Security\BlockEvent\BlockEventService;
use App\Security\BlockEvent\BlockEventTypeEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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
    public function getTreeItem(
        string                           $hash,
        ShareHashRequest                 $requestModel,
        Request                          $request,
        DriveShareGetStructByHashUseCase $getStructByHashUseCase,
        DriveGetTreeItemUseCase          $getTreeItemUseCase,
    ): JsonResponse
    {
        $requestModel->hash = $hash;
        if (!$requestModel->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        try {
            $structEntity = $getStructByHashUseCase->handle($requestModel->hash);

            return new JsonResponse(
                DriveTreeResponse::fromDriveTreeDTO(
                    $getTreeItemUseCase->handle(
                        structId: $structEntity->getId(),
                        userId: $structEntity->getUserID(),
                    )
                ),
                Response::HTTP_OK
            );
        } catch (AbstractLogicException $e) {
            if ($e instanceof DriveShareNotFoundException || $e instanceof DriveFileNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/drive-share/{hash}/full-size', name: 'drive_share.get_full_size_by_hash', methods: ['GET'])]
    public function getStructFullSize(
        string                           $hash,
        ShareHashRequest                 $requestModel,
        Request                          $request,
        DriveShareGetStructByHashUseCase $getStructByHashUseCase,
        DriveGetStructFullSizeUseCase    $getStructFullSizeUseCase,
    ): JsonResponse
    {
        $requestModel->hash = $hash;
        if (!$requestModel->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        try {
            $structEntity = $getStructByHashUseCase->handle($requestModel->hash);
            $fileSize = $getStructFullSizeUseCase->handle(
                structId: $structEntity->getId(),
                userId: $structEntity->getUserID()
            );

            return new JsonResponse(
                DriveGetItemFullSizeResponse::fromFileSizeVO($fileSize),
                Response::HTTP_OK
            );
        } catch (AbstractLogicException $e) {
            if ($e instanceof DriveShareNotFoundException || $e instanceof DriveFileNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/drive-share/{hash}/chunks-info', name: 'drive_share.get_chunks_info_by_hash', methods: ['GET'])]
    public function chunksInfo(
        string $hash,
        ShareHashRequest $requestModel,
        Request $request,
        DriveShareGetStructByHashUseCase $getStructByHashUseCase,
        DriveGetChunksInfoUseCase $getChunksInfoUseCase
    ): JsonResponse
    {
        $requestModel->hash = $hash;
        if (!$requestModel->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        try {
            $structEntity = $getStructByHashUseCase->handle($requestModel->hash);
            $info = $getChunksInfoUseCase->handle(
                structId: $structEntity->getId(),
                userId: $structEntity->getUserID(),
            );

            return new JsonResponse(
                new DriveChunksInfoResponse(start_number: $info->startNumber, end_number: $info->endNumber),
                Response::HTTP_OK
            );
        } catch (AbstractLogicException $e) {
            if (
                $e instanceof DriveStructNotFoundException
                || $e instanceof DriveShareNotFoundException
                || $e instanceof DriveFileNotFoundException
            ) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(
        path: '/api/drive-share/{hash}/chunks/{chunkNumber}',
        name: 'drive_share.get_chunk_by_number_by_hash',
        methods: ['GET']
    )]
    public function getChunkByNumber(
        string $hash,
        int $chunkNumber,
        ShareHashRequest $requestModelForHash,
        IDRequest $requestModelForChunkNumber,
        Request $request,
        DriveShareGetStructByHashUseCase $getStructByHashUseCase,
        DriveGetChunkByNumberUseCase $useCase
    ): BinaryFileResponse
    {
        $requestModelForHash->hash = $hash;
        if (!$requestModelForHash->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModelForHash->getFirstError());
        }

        $requestModelForChunkNumber->id = $chunkNumber;
        if (!$requestModelForChunkNumber->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModelForChunkNumber->getFirstError());
        }

        try {
            $structEntity = $getStructByHashUseCase->handle($requestModelForHash->hash);

            $fileDTO = $useCase->handle(
                structId: $structEntity->getId(),
                chunkNumber: $chunkNumber,
                userId: $structEntity->getUserId()
            );
            $response = new BinaryFileResponse($fileDTO->getFile());
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $fileDTO->getOriginalName()
            );
            return $response;
        } catch (AbstractLogicException $e) {
            if ($e instanceof DriveStructNotFoundException || $e instanceof DriveShareNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/drive-share/{hash}/download', name: 'drive_share.get_file_by_hash', methods: ['GET'])]
    public function getFile(
        string $hash,
        ShareHashRequest $requestModelForHash,
        Request $request,
        DriveShareGetStructByHashUseCase $getStructByHashUseCase,
        DriveGetFileUseCase $getFileUseCase
    ): BinaryFileResponse
    {
        $requestModelForHash->hash = $hash;
        if (!$requestModelForHash->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModelForHash->getFirstError());
        }

        try {
            $structEntity = $getStructByHashUseCase->handle($requestModelForHash->hash);

            $fileDTO = $getFileUseCase->handle(
                structId: $structEntity->getId(),
                userId: $structEntity->getUserId()
            );

            $response = new BinaryFileResponse($fileDTO->getFile());
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $fileDTO->getOriginalName()
            );
            return $response;
        } catch (AbstractLogicException $e) {
            if ($e instanceof DriveFileNotFoundException || $e instanceof DriveShareNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/drive-share/{hash}/archive/create', name: 'drive_share.create_archive_by_hash', methods: ['POST'])]
    public function createArchive(
        string $hash,
        ShareHashRequest $requestModelForHash,
        Request $request,
        DriveShareGetStructByHashUseCase $getStructByHashUseCase,
        DriveArchiveRequestForCreationUseCase $useCase
    ): JsonResponse
    {
        $requestModelForHash->hash = $hash;
        if (!$requestModelForHash->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModelForHash->getFirstError());
        }

        try {
            $structEntity = $getStructByHashUseCase->handle($requestModelForHash->hash);

            $driveArchiveJobEntity = $useCase->handle(
                userId: $structEntity->getUserId(),
                structIds: [$structEntity->getId()],
                place: DriveArchiveJobPlaceEnum::DriveShare,
                needCompareStructIds: true,
            );
            return new JsonResponse(
                DriveArchiveJobResponse::fromDriveArchiveJobEntity($driveArchiveJobEntity),
                Response::HTTP_CREATED
            );
        } catch(DriveArchiveExistsActiveJobsException $e) {
            throw new UnprocessableEntityHttpException(
                message: Lang::t($e->getErrorKey()),
                code: -1
            );
        } catch (AbstractLogicException $e) {
            if ($e instanceof DriveStructNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(
        path: '/api/drive-share/{hash}/archive/status',
        name: 'drive_share.get_archive_status_by_hash',
        methods: ['GET']
    )]
    public function getArchiveStatus(
        string $hash,
        ShareHashRequest $requestModelForHash,
        Request $request,
        DriveShareGetStructByHashUseCase $getStructByHashUseCase,
    ): JsonResponse
    {
        $requestModelForHash->hash = $hash;
        if (!$requestModelForHash->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModelForHash->getFirstError());
        }

        try {

        } catch (AbstractLogicException $e) {
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }
}
