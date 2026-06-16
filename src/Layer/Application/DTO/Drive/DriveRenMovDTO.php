<?php

declare(strict_types=1);

namespace App\Layer\Application\DTO\Drive;

final readonly class DriveRenMovDTO
{
    /** @param int[] $structIds */
    public function __construct(
        public ?int $parentId,
        public array $structIds,
    ) {}
}