<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController extends AbstractController
{
    #[Route(path: '/api/auth/register', name: 'auth.register', methods: ['POST'])]
    public function register()
    {
        var_dump('here');
        exit();
    }
}
