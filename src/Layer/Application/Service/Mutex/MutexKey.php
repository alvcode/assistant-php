<?php

declare(strict_types=1);

namespace App\Layer\Application\Service\Mutex;

final readonly class MutexKey implements \Stringable
{
    private const SEPARATOR = ':';

    /**
     * @param list<string> $parts
     */
    private function __construct(
        private string $scope,
        private array $parts,
    ) {
    }

    public static function for(MutexScope $scope, string|int ...$parts): self
    {
        $normalizedParts = [];

        foreach ($parts as $part) {
            $part = (string) $part;

            if ('' === $part) {
                throw new \InvalidArgumentException('Mutex key parts cannot be empty.');
            }

            if (str_contains($part, self::SEPARATOR)) {
                throw new \InvalidArgumentException(sprintf(
                    'Mutex key part "%s" contains the reserved separator "%s".',
                    $part,
                    self::SEPARATOR,
                ));
            }

            $normalizedParts[] = $part;
        }

        return new self($scope->value, $normalizedParts);
    }

    public function __toString(): string
    {
        return implode(self::SEPARATOR, [$this->scope, ...$this->parts]);
    }
}
