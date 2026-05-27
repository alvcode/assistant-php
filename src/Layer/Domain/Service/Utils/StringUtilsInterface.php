<?php

declare(strict_types=1);

namespace App\Layer\Domain\Service\Utils;

interface StringUtilsInterface
{
    public function truncateString(string $input, int $maxLength): string;

    public function removeHtmlTags(string $input): string;
}
