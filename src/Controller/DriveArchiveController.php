<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\NeedAuth;
use App\Entity\UserEntity;
use App\Infrastructure\Lang;
use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Application\Exception\DriveArchive\DriveArchiveJobNotFoundException;
use App\Layer\Application\UseCase\DriveArchive\DriveArchiveGetActiveJobUseCase;
use App\Layer\Application\UseCase\DriveArchive\DriveArchiveGetChunkByNumberUseCase;
use App\Layer\Application\UseCase\DriveArchive\DriveArchiveGetChunksInfoUseCase;
use App\Layer\Application\UseCase\DriveArchive\DriveArchiveRemoveUseCase;
use App\Layer\Application\UseCase\DriveArchive\DriveArchiveRequestForCreationUseCase;
use App\Layer\Domain\Exception\AbstractLogicException;
use App\Request\DriveArchive\DriveArchiveCreateRequest;
use App\Response\Drive\DriveChunksInfoResponse;
use App\Response\DriveArchive\DriveArchiveJobResponse;
use App\Security\BlockEvent\BlockEventService;
use App\Security\BlockEvent\BlockEventTypeEnum;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class DriveArchiveController extends AbstractController
{
    public function __construct(
        private readonly BlockEventService $blockEventService,
    ) {}

    /**
     * @throws Exception
     */
    #[Route(path: '/api/drive-archive', name: 'drive_archive.create', methods: ['POST'])]
    #[NeedAuth]
    public function create(
        Request $request,
        DriveArchiveCreateRequest $requestModel,
        DriveArchiveRequestForCreationUseCase $useCase
    ): JsonResponse
    {
        if (!$requestModel->populateByRequestBody($request)->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $driveArchiveJobEntity = $useCase->handle($user->id, $requestModel->struct_ids);
            return new JsonResponse(
                DriveArchiveJobResponse::fromDriveArchiveJobEntity($driveArchiveJobEntity),
                Response::HTTP_CREATED
            );
        } catch (AbstractLogicException $e) {
            if ($e instanceof DriveStructNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/drive-archive/status', name: 'drive_archive.get_status', methods: ['GET'])]
    #[NeedAuth]
    public function getStatus(DriveArchiveGetActiveJobUseCase $useCase): JsonResponse
    {
        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $driveArchiveJobEntity = $useCase->handle($user->id);
            return new JsonResponse(
                DriveArchiveJobResponse::fromDriveArchiveJobEntity($driveArchiveJobEntity),
                Response::HTTP_CREATED
            );
        } catch (AbstractLogicException $e) {
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/drive-archive/{id}', name: 'drive_archive.remove_generated', methods: ['DELETE'])]
    #[NeedAuth]
    public function removeGenerated(int $id, DriveArchiveRemoveUseCase $useCase): Response
    {
        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $useCase->handle($id, $user->id);
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (AbstractLogicException $e) {
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/drive-archive/{id}/chunks-info', name: 'drive_archive.get_chunks_info', methods: ['GET'])]
    #[NeedAuth]
    public function chunksInfo(int $id, Request $request, DriveArchiveGetChunksInfoUseCase $useCase): JsonResponse
    {
        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $info = $useCase->handle($id, $user->id);

            return new JsonResponse(
                new DriveChunksInfoResponse(start_number: $info->startNumber, end_number: $info->endNumber),
                Response::HTTP_OK
            );
        } catch (AbstractLogicException $e) {
            if ($e instanceof DriveArchiveJobNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(
        path: '/api/drive-archive/files/{id}/chunks/{chunkNumber}',
        name: 'drive_archive.get_chunk_by_number',
        methods: ['GET'])
    ]
    #[NeedAuth]
    public function getChunkByNumber(
        int $id,
        int $chunkNumber,
        DriveArchiveGetChunkByNumberUseCase $useCase
    ): BinaryFileResponse
    {
        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $fileDTO = $useCase->handle($id, $chunkNumber, $user->id);

            $response = new BinaryFileResponse($fileDTO->getFile());
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $fileDTO->getOriginalName()
            );
            return $response;
        } catch (AbstractLogicException $e) {
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }
}
