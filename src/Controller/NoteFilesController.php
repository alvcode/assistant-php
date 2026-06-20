<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\NeedAuth;
use App\Entity\UserEntity;
use App\Infrastructure\Lang;
use App\Layer\Application\DTO\Common\FileDTO;
use App\Layer\Application\Exception\NoteFile\NoteFileNotFoundByHashException;
use App\Layer\Application\UseCase\NoteFile\GetNoteFileByHashUseCase;
use App\Layer\Application\UseCase\NoteFile\UploadNoteFileUseCase;
use App\Layer\Domain\Exception\AbstractLogicException;
use App\Request\NoteFiles\NoteFileUploadRequest;
use App\Response\NoteFile\UploadNoteFileResponse;
use App\Security\BlockEvent\BlockEventService;
use App\Security\BlockEvent\BlockEventTypeEnum;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class NoteFilesController extends AbstractController
{
    public function __construct(
        private readonly BlockEventService $blockEventService,
        private readonly ParameterBagInterface $parameterBag,
    ) {}

    #[Route(path: '/api/files', name: 'note_files.upload', methods: ['POST'])]
    #[NeedAuth]
    public function upload(
        Request $request,
        NoteFileUploadRequest $requestModel,
        UploadNoteFileUseCase $useCase
    ): JsonResponse
    {
        $requestModel->file = $request->files->get('file');

        if (!$requestModel->validate()) {
            $this->blockEventService->setEvent($request, BlockEventTypeEnum::Validation);
            throw new UnprocessableEntityHttpException($requestModel->getFirstError());
        }

        /** @var UserEntity $user */
        $user = $this->getUser();

        try {
            $noteFileEntity = $useCase->handle(
                new FileDTO(
                    $requestModel->file,
                    $requestModel->file->getClientOriginalExtension(),
                    $requestModel->file->getClientOriginalName(),
                ),
                $user->id
            );
            return new JsonResponse(
                UploadNoteFileResponse::fromNoteFileEntity(
                    entity: $noteFileEntity,
                    downloadBaseUrl: $this->parameterBag->get('app.defaultUri') . '/api/files/hash'
                ),
                Response::HTTP_CREATED
            );
        } catch (AbstractLogicException $e) {
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }

    #[Route(path: '/api/files/hash/{hash}', name: 'note_files.get_by_hash', methods: ['GET'])]
    public function getByHash(string $hash, Request $request, GetNoteFileByHashUseCase $useCase): JsonResponse
    {
        return new JsonResponse(
            [
                'client_ip' => $request->getClientIp(),
                'x_forwarded_for' => $request->headers->get('x-forwarded-for'),
                'x_real_ip' => $request->headers->get('x-real-ip'),
                'remote_addr' => $request->server->get('REMOTE_ADDR'),
                'http_x_forwarded_for' => $request->server->get('HTTP_X_FORWARDED_FOR'),
                'http_x_real_ip' => $request->server->get('HTTP_X_REAL_IP'),
                'all_headers' => $request->headers->all(),
            ],
            Response::HTTP_OK
        );
        
        try {
            $fileDTO = $useCase->handle($hash);

            $response = new BinaryFileResponse($fileDTO->getFile());
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $fileDTO->getOriginalName()
            );
            return $response;
        } catch (AbstractLogicException $e) {
            if ($e instanceof NoteFileNotFoundByHashException) {
                $this->blockEventService->setEvent($request, BlockEventTypeEnum::BruteForce);
            }
            throw new UnprocessableEntityHttpException(Lang::t($e->getErrorKey()));
        }
    }
}
