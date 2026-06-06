<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\NeedAuth;
use App\Entity\UserEntity;
use App\Infrastructure\Lang;
use App\Layer\Application\DTO\Common\FileDTO;
use App\Layer\Application\Exception\NoteCategory\NoteCategoryNotFoundException;
use App\Layer\Application\UseCase\NoteFile\UploadNoteFileUseCase;
use App\Layer\Domain\Exception\AbstractLogicException;
use App\Request\NoteFiles\NoteFileUploadRequest;
use App\Security\BlockEvent\BlockEventService;
use App\Security\BlockEvent\BlockEventTypeEnum;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class NoteFilesController extends AbstractController
{
    public function __construct(
        private readonly BlockEventService $blockEventService,
    ) {}

    #[Route(path: '/api/files', name: 'note_files.upload', methods: ['POST'])]
    #[NeedAuth]
    public function upload(Request $request, NoteFileUploadRequest $requestModel, UploadNoteFileUseCase $useCase)
    {
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
                    $requestModel->file,
                    $requestModel->file->getClientOriginalExtension()
                ),
                $user->id
            );
        } catch (AbstractLogicException $e) {
            if ($e instanceof NoteCategoryNotFoundException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }

        dd($requestModel->file);
    }
}
