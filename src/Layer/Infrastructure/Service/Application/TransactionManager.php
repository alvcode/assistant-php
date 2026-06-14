<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Service\Application;

use App\Layer\Application\Service\TransactionManagerInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TransactionManager implements TransactionManagerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function transactional(callable $callback): mixed 
    {
        $conn = $this->entityManager->getConnection();
        $conn->beginTransaction();

        try {
            $result = $callback();
            $conn->commit();
            return $result;
        } catch (\Throwable $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}