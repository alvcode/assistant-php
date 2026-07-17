<?php

declare(strict_types=1);

namespace App\Controller;

use App\Attribute\NeedAuth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

final class LongPollingController extends AbstractController
{
    private const int DURATION_SECONDS = 5;

    #[Route(path: '/api/long-polling/pool', name: 'long_polling.pool', methods: ['GET'])]
    #[NeedAuth]
    public function pool()
    {
        $endTime = strtotime(sprintf('+%d seconds', self::DURATION_SECONDS));
        $res = [];
        while ($endTime > time()) {
            $res[] = 'tick';
            sleep(1);
        }
        var_dump($res);
        exit();
    }
}
