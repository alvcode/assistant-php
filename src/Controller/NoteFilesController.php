<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\NeedAuth;
use App\Request\NoteFiles\NoteFileUploadRequest;
use App\Security\BlockEvent\BlockEventService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class NoteFilesController extends AbstractController
{
    public function __construct(
        private readonly BlockEventService $blockEventService,
    ) {}

    #[Route(path: '/api/files', name: 'note_files.upload', methods: ['POST'])]
    #[NeedAuth]
    public function upload(Request $request, NoteFileUploadRequest $requestModel)
    {
        $requestModel->file = $request->files->get('file');

        if (!$requestModel->validate()) {
            dd($requestModel->getErrors());
        }

        dd($requestModel->file);
    }
}
