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
        $result = explode('/', trim($this->path, '/'));
        if (count($result) === 1 && $result[0] === '') {
            return [];
        }
        return $result;
    }
}
