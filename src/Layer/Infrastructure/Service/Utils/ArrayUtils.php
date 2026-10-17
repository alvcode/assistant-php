<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Service\Utils;

use App\Layer\Domain\Service\Utils\ArrayUtilsInterface;
use Generator;

final readonly class ArrayUtils implements ArrayUtilsInterface
{
    /** @inheritDoc */
    public function arrayChunk(iterable $items, int $size): Generator
    {
        $batch = [];

        $count = 0;
        foreach ($items as $item) {
            $batch[] = $item;

            if (++$count === $size) {
                yield $batch;
                $batch = [];
                $count = 0;
            }
        }

        if ($batch !== []) {
            yield $batch;
        }
    }
}
