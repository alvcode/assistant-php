<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\NeedAuth;
use App\Entity\UserEntity;
use App\Infrastructure\Lang;
use App\Layer\Application\DTO\Common\FileDTO;
use App\Layer\Application\DTO\Drive\DriveCreateDirectoryDTO;
use App\Layer\Application\DTO\Drive\DriveUploadFileDTO;
use App\Layer\Application\UseCase\Drive\DriveCreateDirectoryUseCase;
use App\Layer\Application\UseCase\Drive\DriveGetTreeUseCase;
use App\Layer\Application\UseCase\Drive\DriveUploadFileUseCase;
use App\Layer\Domain\Exception\AbstractLogicException;
use App\Request\Drive\DriveCreateDirectoryRequest;
use App\Request\Drive\DriveUploadFileRequest;
use App\Request\Drive\DriveWithParentIDRequest;
use App\Response\Drive\DriveTreeResponse;
use App\Security\BlockEvent\BlockEventService;
use App\Security\BlockEvent\BlockEventTypeEnum;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class DriveController extends AbstractController
{
    public function __construct(
        private readonly BlockEventService $blockEventService,
    ) {}

    /**
     * @throws Exception
     */
    #[Route(path: '/api/drive/directories', name: 'drive.create_directory', methods: ['POST'])]
    #[NeedAuth]
    public function createDirectory(
        Request $request, 
        DriveCreateDirectoryRequest $requestModel, 
        DriveCreateDirectoryUseCase $useCase,
        DriveGetTreeUseCase $getTreeUseCase
    ): JsonResponse
    {
        if (!$requestModel->populateByRequest($request)->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $useCase->handle(
                new DriveCreateDirectoryDTO(
                    name: $requestModel->name,
                    parentId: $requestModel->parent_id
                ),
                userId: $user->id
            );

            $driveTree = $getTreeUseCase->handle(
                $user->id,
                $requestModel->parent_id ? (int)$requestModel->parent_id : null
            );

            return new JsonResponse(
                DriveTreeResponse::fromDriveTreeDTOs($driveTree),
                Response::HTTP_OK
            );
        } catch (AbstractLogicException $e) {
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/api/drive/tree', name: 'drive.get_tree', methods: ['GET'])]
    #[NeedAuth]
    public function getTree(
        Request $request,
        DriveWithParentIDRequest $requestModel,
        DriveGetTreeUseCase $useCase
    ): Response
    {
        if (!$requestModel->populateByRequest($request)->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $driveTree = $useCase->handle(
                $user->id,
                $requestModel->parentId ? (int)$requestModel->parentId : null
            );

            return new JsonResponse(
                DriveTreeResponse::fromDriveTreeDTOs($driveTree),
                Response::HTTP_OK
            );
        } catch (AbstractLogicException $e) {
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/drive/upload-file', name: 'drive.upload_file', methods: ['POST'])]
    #[NeedAuth]
    public function uploadFile(
        Request $request, 
        DriveUploadFileRequest $requestModel,
        DriveUploadFileUseCase $useCase,
        DriveGetTreeUseCase $getTreeUseCase
    ): JsonResponse
    {
        $requestModel->populateByArray($request->query->all());
        $requestModel->file = $request->files->get('file');
        if (!$requestModel->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $useCase->handle(
                new FileDTO(
                    file: $requestModel->file, 
                    originalExtension: $requestModel->file->getClientOriginalExtension(), 
                    originalName: $requestModel->file->getClientOriginalName()
                ),
                new DriveUploadFileDTO(parentId: $requestModel->parentId, sha256: $requestModel->sha256),
                $user->id
            );

            $driveTree = $getTreeUseCase->handle(
                $user->id,
                $requestModel->parentId ? (int)$requestModel->parentId : null
            );

            return new JsonResponse(
                DriveTreeResponse::fromDriveTreeDTOs($driveTree),
                Response::HTTP_OK
            );
        } catch (AbstractLogicException $e) {
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }
}
