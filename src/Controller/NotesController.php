<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\NeedAuth;
use App\Entity\UserEntity;
use App\Infrastructure\Lang;
use App\Layer\Application\DTO\Note\CreateNoteDTO;
use App\Layer\Application\DTO\Note\UpdateNoteDTO;
use App\Layer\Application\Exception\Note\NoteNotFoundException;
use App\Layer\Application\Exception\NoteCategory\NoteCategoryNotFoundException;
use App\Layer\Application\UseCase\Note\CreateNoteUseCase;
use App\Layer\Application\UseCase\Note\DeleteNoteUseCase;
use App\Layer\Application\UseCase\Note\GetAllNotesByCategoryUseCase;
use App\Layer\Application\UseCase\Note\GetOneNoteUseCase;
use App\Layer\Application\UseCase\Note\UpdateNoteUseCase;
use App\Layer\Domain\Exception\AbstractLogicException;
use App\Request\Notes\CreateNoteRequest;
use App\Request\Notes\GetAllNotesRequest;
use App\Request\Notes\UpdateNoteRequest;
use App\Response\Note\NoteListResponse;
use App\Response\Note\NoteResponse;
use App\Security\BlockEvent\BlockEventService;
use App\Security\BlockEvent\BlockEventTypeEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class NotesController extends AbstractController
{
    public function __construct(
        private readonly BlockEventService $blockEventService,
    ) {}

    #[Route(path: '/api/notes', name: 'notes.create', methods: ['POST'])]
    #[NeedAuth]
    public function create(
        Request $request,
        CreateNoteRequest $requestModel,
        CreateNoteUseCase $useCase,
    ): JsonResponse
    {
        if (!$requestModel->populateByRequest($request)->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $noteEntity = $useCase->handle(
                new CreateNoteDTO(
                    categoryId: $requestModel->category_id,
                    title: $requestModel->title,
                    noteBlocks: $requestModel->note_blocks,
                ),
                $user->id
            );

            return new JsonResponse(
                NoteResponse::fromNoteEntity($noteEntity),
                Response::HTTP_CREATED
            );
        } catch (AbstractLogicException $e) {
            if ($e instanceof NoteCategoryNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/notes', name: 'notes.get_all', methods: ['GET'])]
    #[NeedAuth]
    public function listByCategory(
        Request $request,
        GetAllNotesRequest $requestModel,
        GetAllNotesByCategoryUseCase $useCase,
    ): JsonResponse
    {
        if (!$requestModel->populateByRequest($request)->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $noteListAggregates = $useCase->handle((int)$requestModel->categoryId, $user->id);

            return new JsonResponse(
                NoteListResponse::fromNoteListAggregates($noteListAggregates),
                Response::HTTP_OK
            );
        } catch (AbstractLogicException $e) {
            if ($e instanceof NoteCategoryNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/notes', name: 'notes.update', methods: ['PATCH'])]
    #[NeedAuth]
    public function update(
        Request $request,
        UpdateNoteRequest $requestModel,
        UpdateNoteUseCase $useCase,
    ): JsonResponse
    {
        if (!$requestModel->populateByRequest($request)->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $noteEntity = $useCase->handle(
                new UpdateNoteDTO(
                    id: $requestModel->id,
                    categoryId: $requestModel->category_id,
                    title: $requestModel->title,
                    noteBlocks: $requestModel->note_blocks,
                ),
                $user->id
            );

            return new JsonResponse(
                NoteResponse::fromNoteEntity($noteEntity),
                Response::HTTP_CREATED
            );
        } catch (AbstractLogicException $e) {
            if ($e instanceof NoteNotFoundException || $e instanceof NoteCategoryNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/notes/{id}', name: 'notes.get_one', methods: ['GET'])]
    #[NeedAuth]
    public function getOne(int $id, Request $request, GetOneNoteUseCase $useCase): JsonResponse
    {
        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $noteEntity = $useCase->handle($id, $user->id);

            return new JsonResponse(
                NoteResponse::fromNoteEntity($noteEntity),
                Response::HTTP_CREATED
            );
        } catch (AbstractLogicException $e) {
            if ($e instanceof NoteNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/notes/{id}', name: 'notes.delete_one', methods: ['DELETE'])]
    #[NeedAuth]
    public function deleteOne(int $id, Request $request, DeleteNoteUseCase $useCase): Response
    {
        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $useCase->handle($id, $user->id);
            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (AbstractLogicException $e) {
            if ($e instanceof NoteNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/notes-share/{hash}/one', name: 'notes.get_hash_one', methods: ['GET'])]
    public function getByHash(string $hash, Request $request)
    {

    }
}
