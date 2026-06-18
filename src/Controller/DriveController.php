<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\NeedAuth;
use App\Entity\UserEntity;
use App\Infrastructure\Lang;
use App\Layer\Application\DTO\Common\FileDTO;
use App\Layer\Application\DTO\Drive\DriveChunkPrepareDTO;
use App\Layer\Application\DTO\Drive\DriveCreateDirectoryDTO;
use App\Layer\Application\DTO\Drive\DriveRenMovDTO;
use App\Layer\Application\DTO\Drive\DriveUploadChunkDTO;
use App\Layer\Application\DTO\Drive\DriveUploadFileDTO;
use App\Layer\Application\Exception\Drive\DriveFileNotFoundException;
use App\Layer\Application\Exception\Drive\DriveNotSafeFilenameException;
use App\Layer\Application\Exception\Drive\DriveParentIdNotFoundException;
use App\Layer\Application\Exception\Drive\DriveRelocatableStructureNotFoundException;
use App\Layer\Application\Exception\Drive\DriveStructNotFoundException;
use App\Layer\Application\UseCase\Drive\DriveChunkEndUseCase;
use App\Layer\Application\UseCase\Drive\DriveChunkPrepareUseCase;
use App\Layer\Application\UseCase\Drive\DriveChunkUploadUseCase;
use App\Layer\Application\UseCase\Drive\DriveCreateDirectoryUseCase;
use App\Layer\Application\UseCase\Drive\DriveDeleteStructUseCase;
use App\Layer\Application\UseCase\Drive\DriveGetChunkByNumberUseCase;
use App\Layer\Application\UseCase\Drive\DriveGetChunksInfoUseCase;
use App\Layer\Application\UseCase\Drive\DriveGetFileUseCase;
use App\Layer\Application\UseCase\Drive\DriveGetFreeSpaceUseCase;
use App\Layer\Application\UseCase\Drive\DriveGetTreeUseCase;
use App\Layer\Application\UseCase\Drive\DriveRenameStructUseCase;
use App\Layer\Application\UseCase\Drive\DriveRenMovStructUseCase;
use App\Layer\Application\UseCase\Drive\DriveUpdateFileHashUseCase;
use App\Layer\Application\UseCase\Drive\DriveUploadFileUseCase;
use App\Layer\Domain\Dict\Common\FileSizeTypeEnum;
use App\Layer\Domain\Exception\AbstractLogicException;
use App\Layer\Domain\ValueObject\FileSizeVO;
use App\Request\Drive\DriveChunkEndRequest;
use App\Request\Drive\DriveChunkPrepareRequest;
use App\Request\Drive\DriveCreateDirectoryRequest;
use App\Request\Drive\DriveRenameStructRequest;
use App\Request\Drive\DriveRenMovStructsRequest;
use App\Request\Drive\DriveUploadChunkRequest;
use App\Request\Drive\DriveUploadFileRequest;
use App\Request\Drive\DriveWithParentIDRequest;
use App\Response\Drive\DriveChunksInfoResponse;
use App\Response\Drive\DriveGetFreeSpaceResponse;
use App\Response\Drive\DriveStructIdResponse;
use App\Response\Drive\DriveTreeResponse;
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
                new DriveUploadFileDTO(
                    parentId: $requestModel->parentId ? (int)$requestModel->parentId : null, 
                    sha256: $requestModel->sha256
                ),
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

    #[Route(path: '/api/drive/files/{id}', name: 'drive.get_file', methods: ['GET'])]
    #[NeedAuth]
    public function getFile(int $id, DriveGetFileUseCase $useCase): BinaryFileResponse
    {
        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $fileDTO = $useCase->handle($id, $user->id);

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

    #[Route(path: '/api/drive/{id}', name: 'drive.delete_file', methods: ['DELETE'])]
    #[NeedAuth]
    public function deleteFile(int $id, DriveDeleteStructUseCase $useCase): Response
    {
        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $useCase->handle($id, $user->id);

            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (AbstractLogicException $e) {
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/drive/files/{id}/rename', name: 'drive.rename_struct', methods: ['PATCH'])]
    #[NeedAuth]
    public function renameStruct(
        int $id, 
        Request $request, 
        DriveRenameStructRequest $requestModel, 
        DriveRenameStructUseCase $useCase
    ): Response
    {
        if (!$requestModel->populateByRequest($request)->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        /** @var UserEntity $user */
        $user = $this->getUser();
            
        try {
            $useCase->handle($id, $requestModel->name, $user->id);
            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (AbstractLogicException $e) {
            if ($e instanceof DriveStructNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/drive/space', name: 'drive.get_free_space', methods: ['GET'])]
    #[NeedAuth]
    public function getFreeSpace(DriveGetFreeSpaceUseCase $useCase): JsonResponse
    {
        /** @var UserEntity $user */
        $user = $this->getUser();
        
        try {
            $freeSpaceDTO = $useCase->handle($user->id);

            return new JsonResponse(
                DriveGetFreeSpaceResponse::fromDriveGetFreeSpaceDTO($freeSpaceDTO),
                Response::HTTP_OK
            );
        } catch (AbstractLogicException $e) {
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/drive/renmov', name: 'drive.renmov_structs', methods: ['PATCH'])]
    #[NeedAuth]
    public function renMovStructs(
        Request $request, 
        DriveRenMovStructsRequest $requestModel, 
        DriveRenMovStructUseCase $useCase
    ): Response
    {
        if (!$requestModel->populateByRequest($request)->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $useCase->handle(
                new DriveRenMovDTO(parentId: $requestModel->parent_id, structIds: $requestModel->struct_ids),
                $user->id
            );

            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (AbstractLogicException $e) {
            if (
                $e instanceof DriveParentIdNotFoundException 
                || $e instanceof DriveRelocatableStructureNotFoundException
            ) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/drive/chunk-prepare', name: 'drive.chunk_prepare', methods: ['POST'])]
    #[NeedAuth]
    public function chunkPrepare(
        Request $request, 
        DriveChunkPrepareRequest $requestModel,
        DriveChunkPrepareUseCase $useCase
    ): JsonResponse
    {
        if (!$requestModel->populateByRequest($request)->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $structId = $useCase->handle(
                new DriveChunkPrepareDTO(
                    fileName: $requestModel->filename,
                    size: new FileSizeVO(size: (float)$requestModel->full_size, sizeType: FileSizeTypeEnum::Bytes),
                    parentId: $requestModel->parent_id,
                    sha256: $requestModel->sha256,
                ),
                $user->id
            );

            return new JsonResponse(DriveStructIdResponse::fromStructId($structId), Response::HTTP_CREATED);
        } catch (AbstractLogicException $e) {
            if (
                $e instanceof DriveParentIdNotFoundException 
                || $e instanceof DriveNotSafeFilenameException
            ) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/drive/upload-chunk', name: 'drive.upload_chunk', methods: ['POST'])]
    #[NeedAuth]
    public function uploadChunk(
        Request $request, 
        DriveUploadChunkRequest $requestModel,
        DriveChunkUploadUseCase $useCase
    ): Response
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
                new DriveUploadChunkDTO(
                    structId: (int)$requestModel->structId,
                    chunkNumber: (int)$requestModel->chunkNumber
                ),
                $user->id
            );

            return new Response(null, Response::HTTP_CREATED);
        } catch (AbstractLogicException $e) {
            if ($e instanceof DriveStructNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/drive/chunk-end', name: 'drive.chunk_end', methods: ['POST'])]
    #[NeedAuth]
    public function chunkEnd(
        Request $request, 
        DriveChunkEndRequest $requestModel, 
        DriveChunkEndUseCase $useCase
    ): Response
    {
        if (!$requestModel->populateByRequest($request)->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $useCase->handle($requestModel->struct_id, $user->id);
            return new Response(null, Response::HTTP_OK);
        } catch (AbstractLogicException $e) {
            if ($e instanceof DriveStructNotFoundException || $e instanceof DriveFileNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/drive/files/{id}/chunks-info', name: 'drive.chunks_info', methods: ['GET'])]
    #[NeedAuth]
    public function chunksInfo(int $id, Request $request, DriveGetChunksInfoUseCase $useCase): JsonResponse
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
            if ($e instanceof DriveStructNotFoundException || $e instanceof DriveFileNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/drive/files/{id}/chunks/{chunkNumber}', name: 'drive.get_chunk_by_number', methods: ['GET'])]
    #[NeedAuth]
    public function getChunkByNumber(
        int $id, 
        int $chunkNumber, 
        Request $request, 
        DriveGetChunkByNumberUseCase $useCase
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
            if ($e instanceof DriveStructNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/drive/files/{id}/sha256/{hash}', name: 'drive.update_file_hash', methods: ['PATCH'])]
    #[NeedAuth]
    public function updateFileHash(int $id, string $hash, DriveUpdateFileHashUseCase $useCase): Response
    {
        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $useCase->handle($id, $hash, $user->id);
            return new Response(null, Response::HTTP_CREATED);
        } catch (AbstractLogicException $e) {
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }
}
