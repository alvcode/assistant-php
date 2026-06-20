<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\NeedAuth;
use App\Entity\UserEntity;
use App\Infrastructure\Lang;
use App\Layer\Application\DTO\NoteCategory\CreateNoteCategoryDTO;
use App\Layer\Application\DTO\NoteCategory\UpdateNoteCategoryDTO;
use App\Layer\Application\Exception\NoteCategory\NoteCategoryNotFoundException;
use App\Layer\Application\Exception\NoteCategory\ParentNoteCategoryNotFoundException;
use App\Layer\Application\UseCase\NoteCategory\CreateNoteCategoryUseCase;
use App\Layer\Application\UseCase\NoteCategory\DeleteNoteCategoryUseCase;
use App\Layer\Application\UseCase\NoteCategory\ListNoteCategoryUseCase;
use App\Layer\Application\UseCase\NoteCategory\NoteCategoryPositionUpUseCase;
use App\Layer\Application\UseCase\NoteCategory\UpdateNoteCategoryUseCase;
use App\Layer\Domain\Exception\AbstractLogicException;
use App\Request\Common\IDRequest;
use App\Request\NoteCategories\CreateNoteCategoryRequest;
use App\Response\NoteCategory\NoteCategoryResponse;
use App\Security\BlockEvent\BlockEventService;
use App\Security\BlockEvent\BlockEventTypeEnum;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class NoteCategoriesController extends AbstractController
{
    public function __construct(
        private readonly BlockEventService $blockEventService,
    ) {}

    /**
     * @throws Exception
     */
    #[Route(path: '/api/note-categories', name: 'note_categories.create', methods: ['POST'])]
    #[NeedAuth]
    public function create(
        Request $request,
        CreateNoteCategoryRequest $requestModel,
        CreateNoteCategoryUseCase $useCase,
    ): JsonResponse
    {
        if (!$requestModel->populateByRequest($request)->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $noteCategoryEntity = $useCase->handle(
                new CreateNoteCategoryDTO(
                    userId: $user->id,
                    name: $requestModel->name,
                    parentId: $requestModel->parent_id,
                )
            );

            return new JsonResponse(
                NoteCategoryResponse::fromNoteCategoryEntity($noteCategoryEntity),
                Response::HTTP_CREATED
            );
        } catch (AbstractLogicException $e) {
            if ($e instanceof ParentNoteCategoryNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/note-categories', name: 'note_categories.list', methods: ['GET'])]
    #[NeedAuth]
    public function list(
        ListNoteCategoryUseCase $useCase,
    ): JsonResponse
    {
        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $entities = $useCase->handle($user->id);
            return new JsonResponse(NoteCategoryResponse::fromNoteCategoryEntities($entities), Response::HTTP_OK);
        } catch (AbstractLogicException $e) {
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/note-categories/{id}', name: 'note_categories.delete_one', methods: ['DELETE'])]
    #[NeedAuth]
    public function deleteOne(
        int $id,
        Request $request,
        DeleteNoteCategoryUseCase $useCase,
    ): Response
    {
        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $useCase->handle($id, $user->id);
            return new Response('', Response::HTTP_NO_CONTENT);
        } catch (AbstractLogicException $e) {
            if ($e instanceof NoteCategoryNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/note-categories/{id}', name: 'note_categories.update', methods: ['PATCH'])]
    #[NeedAuth]
    public function update(
        int $id,
        Request $request,
        CreateNoteCategoryRequest $requestModel,
        UpdateNoteCategoryUseCase $useCase,
    ): JsonResponse
    {
        if (!$requestModel->populateByRequest($request)->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $noteCategoryEntity = $useCase->handle(
                new UpdateNoteCategoryDTO(
                    id: $id,
                    name: $requestModel->name,
                    parentId: $requestModel->parent_id,
                ),
                $user->id
            );
            return new JsonResponse(
                NoteCategoryResponse::fromNoteCategoryEntity($noteCategoryEntity),
                Response::HTTP_CREATED
            );
        } catch (AbstractLogicException $e) {
            if ($e instanceof NoteCategoryNotFoundException || $e instanceof ParentNoteCategoryNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/note-categories/position-up', name: 'note_categories.position_up', methods: ['POST'])]
    #[NeedAuth]
    public function positionUp(
        Request $request,
        IDRequest $requestModel,
        NoteCategoryPositionUpUseCase $useCase,
    ): Response
    {
        if (!$requestModel->populateByRequest($request)->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $useCase->handle($requestModel->id, $user->id);
            return new Response('', Response::HTTP_NO_CONTENT);
        } catch (AbstractLogicException $e) {
            if ($e instanceof NoteCategoryNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }
}
