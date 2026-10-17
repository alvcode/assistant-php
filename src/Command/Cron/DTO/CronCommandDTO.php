<?php

declare(strict_types=1);

namespace App\Command\Cron\DTO;

final readonly class CronCommandDTO
{
    public function __construct(
        public string $name,
        public ?array $arguments = null,
        public ?array $options = null,
    )
    {
    }
}
