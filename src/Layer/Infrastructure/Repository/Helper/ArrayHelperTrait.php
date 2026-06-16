<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository\Helper;

use Generator;

trait ArrayHelperTrait
{
    /**
     * @param iterable<mixed,mixed> $items
     */
    public function arrayChunk(iterable $items, int $size): Generator
    {
        $batch = [];

        foreach ($items as $item) {
            $batch[] = $item;

            if (count($batch) === $size) {
                yield $batch;
                $batch = [];
            }
        }

        if ($batch !== []) {
            yield $batch;
        }
    }
}