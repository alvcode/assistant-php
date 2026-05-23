<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\NeedAuth;
use App\Entity\UserEntity;
use App\Infrastructure\Lang;
use App\Layer\Application\DTO\NoteCategory\CreateNoteCategoryDTO;
use App\Layer\Application\Exception\NoteCategory\ParentNoteCategoryNotFoundException;
use App\Layer\Application\UseCase\NoteCategory\CreateNoteCategoryUseCase;
use App\Layer\Application\UseCase\NoteCategory\ListNoteCategoryUseCase;
use App\Layer\Domain\Exception\AbstractLogicException;
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
}
