<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository\Helper;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Generator;

trait EachLowCostTrait
{
    /**
     * @param EntityManagerInterface $entityManager
     * @param string $query прим. "select * from table_name"
     * @param string $where прим. "age > :user_age and height >= :user_height"
     * @param array<string,mixed> $params прим. ['user_age' => 18, 'user_height' => 150]
     * @param array<string,mixed> $types прим. ['file_ids' => ArrayParameterType::INTEGER]
     * @param int $batchSize
     * @param string $iterationField
     * @return Generator<array<string,mixed>>
     * @throws Exception
     */
    public function eachLowCost(
        EntityManagerInterface $entityManager,
        string $query,
        string $where,
        array $params,
        array $types = [],
        int $batchSize = 100,
        string $iterationField = 'id',
    ): Generator
    {
        foreach ($this->batchLowCost(
            $entityManager,
            $query,
            $where,
            $params,
            $types,
            $batchSize,
            $iterationField
        ) as $rows) {
            foreach ($rows as $row) {
                yield $row;
            }
        }
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param string $query прим. "select * from table_name"
     * @param string $where прим. "age > :user_age and height >= :user_height"
     * @param array<string,mixed> $params прим. ['user_age' => 18, 'user_height' => 150]
     * @param array<string,mixed> $types прим. ['file_ids' => ArrayParameterType::INTEGER]
     * @param int $batchSize
     * @param string $iterationField
     * @return Generator
     * @throws Exception
     */
    public function batchLowCost(
        EntityManagerInterface $entityManager,
        string $query,
        string $where,
        array $params,
        array $types = [],
        int $batchSize = 100,
        string $iterationField = 'id',
    ): Generator
    {
        $iterationFieldForArr = $iterationField;
        if (str_contains($iterationField, '.')) {
            $iterationFieldForArr = explode('.', $iterationField, 2)[1];
        }
        $lastId = 0;

        $conn = $entityManager->getConnection();
        while (true) {

            $rows = $conn->executeQuery(
                $this->buildQuery($query, $where, $iterationField, $lastId, $batchSize),
                $params,
                $types
            )->fetchAllAssociative();

            if (empty($rows)) {
                break;
            }

            $lastId = $rows[count($rows) - 1][$iterationFieldForArr];
            yield $rows;
        }
    }

    private function buildQuery(
        string $baseQuery,
        string $where,
        string $iterationField,
        int $lastId,
        int $batchSize
    ): string
    {
        return sprintf(
            "%s where %s %s > %d order by %s limit %d",
            $baseQuery,
            $where,
            empty($where) ? $iterationField : ' and ' . $iterationField,
            $lastId,
            $iterationField,
            $batchSize
        );
    }
}
