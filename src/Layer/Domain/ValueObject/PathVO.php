<?php

declare(strict_types=1);

namespace App\Layer\Domain\ValueObject;

final readonly class PathVO
{
    public function __construct(
        private string $path,
    ) {}

    /** @return string[] */
    public function getAsArray(): array
    {
        return explode('/', trim($this->path, '/'));
    }
}
