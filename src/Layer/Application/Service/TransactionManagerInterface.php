<?php

declare(strict_types=1);

namespace App\Layer\Application\Service;

interface TransactionManagerInterface
{
    public function transactional(callable $callback): mixed;
}