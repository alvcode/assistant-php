<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\NeedAuth;
use App\Request\NoteCategories\CreateNoteCategoryRequest;
use App\Security\BlockEvent\BlockEventService;
use App\Security\BlockEvent\BlockEventTypeEnum;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class NoteCategoriesController extends AbstractController
{
    public function __construct(
        private BlockEventService $blockEventService,
    ) {}

    /**
     * @throws Exception
     */
    #[Route(path: '/api/note-categories', name: 'note_categories.create', methods: ['POST'])]
    #[NeedAuth]
    public function create(
        Request $request,
        CreateNoteCategoryRequest $requestModel,
    )
    {
        if (!$requestModel->populateByRequest($request)->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        dd($requestModel->name, $requestModel->parent_id);
    }
}
