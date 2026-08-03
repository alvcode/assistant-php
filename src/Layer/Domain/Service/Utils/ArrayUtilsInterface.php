<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Utils;

use Generator;

interface ArrayUtilsInterface
{
    /**
     * @param iterable<mixed,mixed> $items
     */
    public function arrayChunk(iterable $items, int $size): Generator;
}
