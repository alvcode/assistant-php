<?php

declare(strict_types=1);

namespace App\Layer\Infrastructure\Repository;

use App\Layer\Domain\Repository\ConfigRepositoryInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final readonly class ConfigRepository implements ConfigRepositoryInterface
{
    public function __construct(
        private ParameterBagInterface $parameterBag,
    ) {}

    /** @inheritDoc */
    public function getNoteFileStorageLimitPerUser(): int
    {
        $config = $this->parameterBag->get('file.limitStoragePerUser');
        return $config * 1000000;
    }

    public function getNoteFileSavePath(): string
    {
        return $this->parameterBag->get('file.savePath');
    }
}
