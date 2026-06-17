<?php

declare(strict_types=1);

namespace App\Response\Drive;

final class DriveStructIdResponse
{
    public function __construct(
        public int $struct_id
    ) {}

    public static function fromStructId(int $structId): self 
    {
        return new self($structId);
    }
}