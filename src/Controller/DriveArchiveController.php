<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\NeedAuth;
use App\Entity\UserEntity;
use App\Infrastructure\Lang;
use App\Layer\Application\UseCase\Drive\DriveCreateDirectoryUseCase;
use App\Layer\Application\UseCase\Drive\DriveGetTreeUseCase;
use App\Layer\Domain\Exception\AbstractLogicException;
use App\Request\Drive\DriveCreateDirectoryRequest;
use App\Security\BlockEvent\BlockEventService;
use App\Security\BlockEvent\BlockEventTypeEnum;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
        DriveCreateDirectoryRequest $requestModel,
        DriveCreateDirectoryUseCase $useCase
    ): JsonResponse
    {
        if (!$requestModel->populateByRequest($request)->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        /** @var UserEntity $user */
        $user = $this->getUser();

        try {

        } catch (AbstractLogicException $e) {
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }
}
